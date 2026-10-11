<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Client;
use App\Models\FirebaseLog;
use App\Models\FirebaseRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request, string $type): View
    {
        abort_unless($request->user()?->canPermission('logs.ver'), 403);
        abort_unless($this->allowed($type), 404);

        $model = $this->model($type);
        $logs = $this->filtered($model::query(), $request, $type)
            ->with('client')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'type' => $type,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, string $type, int $log): View
    {
        abort_unless($request->user()?->canPermission('logs.ver'), 403);
        abort_unless($this->allowed($type), 404);

        $model = $this->model($type);
        $entry = $model::query()->with('client')->findOrFail($log);

        return view('admin.logs.show', [
            'entry' => $entry,
            'type' => $type,
        ]);
    }

    private function allowed(string $type): bool
    {
        return in_array($type, ['api', 'firebase-requests', 'firebase-logs'], true);
    }

    /**
     * @return class-string<Model>
     */
    private function model(string $type): string
    {
        return match ($type) {
            'firebase-requests' => FirebaseRequest::class,
            'firebase-logs' => FirebaseLog::class,
            default => ApiLog::class,
        };
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function filtered(Builder $query, Request $request, string $type): Builder
    {
        return $query
            ->when($request->filled('client_id'), fn (Builder $builder) => $builder->where('client_id', $request->integer('client_id')))
            ->when($request->filled('action'), fn (Builder $builder) => $builder->where('action', $request->string('action')))
            ->when($request->filled('method'), fn (Builder $builder) => $builder->where('method', strtoupper((string) $request->string('method'))))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->integer('status')))
            ->when($request->filled('from'), fn (Builder $builder) => $builder->where('created_at', '>=', Carbon::parse((string) $request->string('from'))->startOfDay()))
            ->when($request->filled('to'), fn (Builder $builder) => $builder->where('created_at', '<=', Carbon::parse((string) $request->string('to'))->endOfDay()))
            ->when($request->filled('notification_id') && $type !== 'api', fn (Builder $builder) => $builder->where('push_notification_id', $request->integer('notification_id')))
            ->when($request->filled('q'), function (Builder $builder) use ($request) {
                $term = '%'.addcslashes((string) $request->string('q'), '%_\\').'%';
                $builder->where(function (Builder $inner) use ($term) {
                    $inner->whereRaw('CAST(payload AS CHAR) LIKE ?', [$term])
                        ->orWhereRaw('CAST(response AS CHAR) LIKE ?', [$term]);
                });
            });
    }
}
