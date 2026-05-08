<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\Franken;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\Franken\FrankenGrpcBackend;
use GrpcLiteGax\Backend\Franken\FrankenGrpcResponse;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Tests\Support\FakeFrankenGrpcBridge;
use PHPUnit\Framework\TestCase;

final class FrankenGrpcBackendTest extends TestCase
{
    public function testDelegatesUnaryRequestToBridge(): void
    {
        $bridge = new FakeFrankenGrpcBridge();
        $bridge->enqueueResponse(new FrankenGrpcResponse(
            payload: 'response-payload',
            metadata: ['response-header' => ['value']],
        ));
        $backend = new FrankenGrpcBackend($bridge);

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
        $bridge = new FakeFrankenGrpcBridge();
        $bridge->enqueueResponse(new FrankenGrpcResponse(
            payload: '',
            statusCode: GrpcStatusCode::UNAVAILABLE,
            statusMessage: 'unavailable',
            metadata: ['status-detail' => ['value']],
        ));
        $backend = new FrankenGrpcBackend($bridge);

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->grpcStatusCode);
        self::assertSame('unavailable', $response->statusMessage);
        self::assertSame(['status-detail' => ['value']], $response->metadata);
    }

    public function testCloseDelegatesToBridgeAndRejectsLaterCalls(): void
    {
        $bridge = new FakeFrankenGrpcBridge();
        $backend = new FrankenGrpcBackend($bridge);

        $backend->close();
        $backend->close();

        self::assertTrue($bridge->isClosed());

        $this->expectException(BackendClosedException::class);

        $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'request-payload'));
    }
}
