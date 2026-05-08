<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\GrpcLite;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteBackend;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteResponse;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Tests\Support\FakeGrpcLiteBridge;
use PHPUnit\Framework\TestCase;

final class GrpcLiteBackendTest extends TestCase
{
    public function testDelegatesUnaryRequestToBridge(): void
    {
        $bridge = new FakeGrpcLiteBridge();
        $bridge->enqueueResponse(new GrpcLiteResponse(
            payload: 'response-payload',
            metadata: ['response-header' => ['value']],
        ));
        $backend = new GrpcLiteBackend($bridge);

        $response = $backend->call(new UnaryRequest(
            service: 'service.v1.Service',
            method: 'Method',
            payload: 'request-payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 1.5,
        ));

        self::assertSame('response-payload', $response->payload);
        self::assertSame(['response-header' => ['value']], $response->metadata);
        self::assertSame([
            'path' => '/service.v1.Service/Method',
            'payload' => 'request-payload',
            'metadata' => ['request-header' => ['value']],
            'timeoutSeconds' => 1.5,
        ], $bridge->lastCall());
    }

    public function testMapsBridgeStatusResponse(): void
    {
        $bridge = new FakeGrpcLiteBridge();
        $bridge->enqueueResponse(new GrpcLiteResponse(
            payload: '',
            statusCode: GrpcStatusCode::UNAVAILABLE,
            statusMessage: 'unavailable',
            metadata: ['grpc-status-details-bin' => ['value']],
        ));
        $backend = new GrpcLiteBackend($bridge);

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->grpcStatusCode);
        self::assertSame('unavailable', $response->statusMessage);
        self::assertSame(['grpc-status-details-bin' => ['value']], $response->metadata);
    }

    public function testCloseDelegatesToBridgeAndRejectsLaterCalls(): void
    {
        $bridge = new FakeGrpcLiteBridge();
        $backend = new GrpcLiteBackend($bridge);

        $backend->close();
        $backend->close();

        self::assertTrue($bridge->isClosed());
        self::assertSame(1, $bridge->closeCallCount());

        $this->expectException(BackendClosedException::class);

        $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));
    }
}
