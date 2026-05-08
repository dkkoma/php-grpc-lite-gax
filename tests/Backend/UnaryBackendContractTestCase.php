<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;
use PHPUnit\Framework\TestCase;

abstract class UnaryBackendContractTestCase extends TestCase
{
    /**
     * @param list<UnaryResponse> $responses
     */
    abstract protected function createBackend(array $responses): UnaryBackend;

    public function testBackendReturnsUnaryResponse(): void
    {
        $backend = $this->createBackend([
            new UnaryResponse('response-payload'),
        ]);

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame('response-payload', $response->payload);
        self::assertSame(GrpcStatusCode::OK, $response->grpcStatusCode);
    }

    public function testBackendPreservesNonOkUnaryResponse(): void
    {
        $backend = $this->createBackend([
            new UnaryResponse('', GrpcStatusCode::UNAVAILABLE, 'unavailable'),
        ]);

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->grpcStatusCode);
        self::assertSame('unavailable', $response->statusMessage);
    }

    public function testBackendCanBeClosed(): void
    {
        $backend = $this->createBackend([]);

        $backend->close();

        $this->addToAssertionCount(1);
    }
}
