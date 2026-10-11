<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use App\Models\Client;
use App\Services\Logging\SensitiveDataMasker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class LogApiRequest
{
    public function __construct(private readonly SensitiveDataMasker $masker) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) str()->uuid();
        $request->attributes->set('request_id', $requestId);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->store($request, $requestId, $this->status($exception), $this->errorBody($exception));

            throw $exception;
        }

        $this->store(
            $request,
            $requestId,
            $response->getStatusCode(),
            $this->decodeResponse($response),
        );

        return $response;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function store(Request $request, string $requestId, int $status, array $response): void
    {
        try {
            $client = $request->attributes->get('client');

            ApiLog::query()->create([
                'request_id' => $requestId,
                'client_id' => $client instanceof Client ? $client->id : null,
                'action' => $this->action($request),
                'method' => $request->method(),
                'status' => $status,
                'payload' => $this->masker->mask([
                    'query' => $request->query(),
                    'body' => $request->except(array_keys($request->allFiles())),
                ]),
                'response' => $this->masker->mask($response),
            ]);
        } catch (Throwable) {
            // Un fallo al persistir el log no debe cortar la API.
        }
    }

    private function action(Request $request): string
    {
        return match ($request->route()?->getName()) {
            'api.v1.register-user' => 'registerUser',
            'api.v1.update-token' => 'updateToken',
            'api.v1.send-push' => 'sendPush',
            'api.v1.show-push' => 'showPush',
            default => (string) ($request->route()?->getName() ?: $request->path()),
        };
    }

    private function status(Throwable $exception): int
    {
        if ($exception instanceof ValidationException) {
            return $exception->status;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * @return array<string, mixed>
     */
    private function errorBody(Throwable $exception): array
    {
        if ($exception instanceof ValidationException) {
            return [
                'success' => false,
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $exception->errors(),
            ];
        }

        return [
            'success' => false,
            'message' => $exception->getMessage() !== '' ? $exception->getMessage() : 'Error en la solicitud.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(Response $response): array
    {
        $decoded = json_decode($response->getContent(), true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return ['raw' => mb_substr($response->getContent(), 0, 2000)];
    }
}
