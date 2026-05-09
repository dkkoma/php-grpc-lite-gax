<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\GrpcLite;

use GrpcLiteGax\Backend\BackendClosedException;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteBackend;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteResponse;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingRequest;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Tests\Support\FakeGrpcLiteBridge;
use GrpcLiteGax\Tests\Support\FakeServerStreamingCall;
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

    public function testDelegatesServerStreamingRequestToBridge(): void
    {
        $bridge = new FakeGrpcLiteBridge();
        $bridge->enqueueServerStreamingCall(new FakeServerStreamingCall(['response-payload']));
        $backend = new GrpcLiteBackend($bridge);

        $call = $backend->start(new ServerStreamingRequest(
            service: 'service.v1.Service',
            method: 'List',
            payload: 'request-payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 2.5,
        ));

        self::assertSame(['response-payload'], iterator_to_array($call->responses()));
        self::assertSame([
            'path' => '/service.v1.Service/List',
            'payload' => 'request-payload',
            'metadata' => ['request-header' => ['value']],
            'timeoutSeconds' => 2.5,
        ], $bridge->lastCall());
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

    public function testRejectsServerStreamingCallsAfterClose(): void
    {
        $backend = new GrpcLiteBackend(new FakeGrpcLiteBridge());

        $backend->close();

        $this->expectException(BackendClosedException::class);

        $backend->start(new ServerStreamingRequest('service.v1.Service', 'List', 'request-payload'));
    }
}
