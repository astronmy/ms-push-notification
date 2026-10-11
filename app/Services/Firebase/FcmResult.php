<?php

namespace App\Services\Firebase;

class FcmResult
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        public bool $successful,
        public bool $invalidToken,
        public bool $retryable,
        public int $status,
        public ?string $errorCode,
        public ?string $message,
        public array $body,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public static function fromHttp(int $status, array $body): self
    {
        $errorCode = self::errorCode($body);
        $message = isset($body['error']['message']) && is_string($body['error']['message'])
            ? $body['error']['message']
            : null;

        if ($status >= 200 && $status < 300) {
            return new self(true, false, false, $status, null, null, $body);
        }

        $invalidToken = self::isInvalidToken($status, $errorCode, $message);
        $retryable = ! $invalidToken && self::isRetryable($status, $errorCode);

        return new self(false, $invalidToken, $retryable, $status, $errorCode, $message, $body);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function errorCode(array $body): ?string
    {
        $details = $body['error']['details'] ?? null;

        if (is_array($details)) {
            foreach ($details as $detail) {
                if (is_array($detail) && isset($detail['errorCode']) && is_string($detail['errorCode'])) {
                    return $detail['errorCode'];
                }
            }
        }

        $status = $body['error']['status'] ?? null;

        return is_string($status) ? $status : null;
    }

    private static function isInvalidToken(int $status, ?string $errorCode, ?string $message): bool
    {
        if (in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND'], true) || $status === 404) {
            return true;
        }

        if ($errorCode !== 'INVALID_ARGUMENT' && $status !== 400) {
            return false;
        }

        $message = strtolower((string) $message);

        return str_contains($message, 'registration token')
            || str_contains($message, 'not a valid fcm')
            || str_contains($message, 'requested entity was not found');
    }

    private static function isRetryable(int $status, ?string $errorCode): bool
    {
        if (in_array($errorCode, ['UNAVAILABLE', 'INTERNAL', 'QUOTA_EXCEEDED'], true)) {
            return true;
        }

        return $status === 429 || $status >= 500 || $status === 0;
    }
}
