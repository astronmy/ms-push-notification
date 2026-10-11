<?php

namespace Tests\Unit;

use App\Services\Firebase\FcmResult;
use App\Services\Logging\SensitiveDataMasker;
use PHPUnit\Framework\TestCase;

class FcmSupportTest extends TestCase
{
    public function test_masks_nested_secrets_and_keeps_a_hint(): void
    {
        $masked = (new SensitiveDataMasker)->mask([
            'firebase_token' => 'abcdefghijklmnopqrstuv',
            'nested' => ['private_key' => 'secret-key-value'],
            'title' => 'Hola',
        ]);

        $this->assertSame('***stuv', $masked['firebase_token']);
        $this->assertSame('***alue', $masked['nested']['private_key']);
        $this->assertSame('Hola', $masked['title']);
    }

    public function test_unregistered_token_is_not_retried(): void
    {
        $result = FcmResult::fromHttp(404, [
            'error' => [
                'code' => 404,
                'message' => 'Requested entity was not found.',
                'status' => 'NOT_FOUND',
                'details' => [
                    ['errorCode' => 'UNREGISTERED'],
                ],
            ],
        ]);

        $this->assertFalse($result->successful);
        $this->assertTrue($result->invalidToken);
        $this->assertFalse($result->retryable);
    }

    public function test_unavailable_is_retryable(): void
    {
        $result = FcmResult::fromHttp(503, [
            'error' => [
                'message' => 'Unavailable',
                'status' => 'UNAVAILABLE',
            ],
        ]);

        $this->assertTrue($result->retryable);
        $this->assertFalse($result->invalidToken);
    }
}
