<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;
use PHPUnit\Framework\TestCase;

abstract class UnaryBackendContractTestCase extends TestCase
{
    abstract protected function createBackendForOkUnary(UnaryResponse $response): UnaryBackend;

    abstract protected function createBackendForStatus(UnaryResponse $response): UnaryBackend;

    abstract protected function createBackendForLifecycle(): UnaryBackend;

    public function testBackendReturnsUnaryResponse(): void
    {
        $backend = $this->createBackendForOkUnary(new UnaryResponse('response-payload'));

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame('response-payload', $response->payload);
        self::assertSame(GrpcStatusCode::OK, $response->grpcStatusCode);
    }

    public function testBackendPreservesNonOkUnaryResponse(): void
    {
        $backend = $this->createBackendForStatus(
            new UnaryResponse('', GrpcStatusCode::UNAVAILABLE, 'unavailable'),
        );

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->grpcStatusCode);
        self::assertSame('unavailable', $response->statusMessage);
    }

    public function testBackendCanBeClosed(): void
    {
        $backend = $this->createBackendForLifecycle();

        $backend->close();
        $backend->close();

        $this->addToAssertionCount(1);
    }

    public function testBackendRejectsCallsAfterClose(): void
    {
        $backend = $this->createBackendForLifecycle();
        $backend->close();

        $this->expectException(BackendClosedException::class);

        $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));
    }
}
