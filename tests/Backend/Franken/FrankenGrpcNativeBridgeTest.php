<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\Franken;

use FrankenGrpc\Channel;
use FrankenGrpc\ServerStreamingCall;
use FrankenGrpc\Status;
use FrankenGrpc\UnaryCall;
use FrankenGrpc\UnaryResult;
use GrpcLiteGax\Backend\Franken\FrankenGrpcNativeBridge;
use GrpcLiteGax\Backend\Franken\FrankenGrpcNativeServerStreamingCall;
use GrpcLiteGax\Backend\GrpcStatusCode;
use PHPUnit\Framework\TestCase;

final class FrankenGrpcNativeBridgeTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Channel::$instances = [];
        UnaryCall::$instances = [];
        UnaryCall::$nextResult = null;
        ServerStreamingCall::$instances = [];
        ServerStreamingCall::$nextResponses = [];
        ServerStreamingCall::$nextStatus = new Status(0);
        ServerStreamingCall::$nextInitialMetadata = [];
        ServerStreamingCall::$nextTrailingMetadata = [];
    }

    public function testCreatesChannelWithOptions(): void
    {
        new FrankenGrpcNativeBridge('localhost:50051', ['plaintext' => true]);

        self::assertCount(1, Channel::$instances);
        self::assertSame('localhost:50051', Channel::$instances[0]->target);
        self::assertSame(['plaintext' => true], Channel::$instances[0]->options);
    }

    public function testMapsUnaryCallToNativeSurface(): void
    {
        UnaryCall::$nextResult = new UnaryResult(
            payload: 'response-payload',
            status: new Status(0, '', ['grpc-status' => ['0']]),
            initialMetadata: ['content-type' => ['application/grpc']],
            trailingMetadata: ['grpc-status-details-bin' => ['details']],
        );
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $response = $bridge->unaryCall(
            path: '/service.v1.Service/Method',
            payload: 'request-payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 1.5,
        );

        self::assertSame('response-payload', $response->payload);
        self::assertSame(GrpcStatusCode::OK, $response->statusCode);
        self::assertSame('', $response->statusMessage);
        self::assertSame(['content-type' => ['application/grpc']], $response->metadata);
        self::assertSame(['grpc-status-details-bin' => ['details']], $response->trailingMetadata);
        self::assertSame('/service.v1.Service/Method', UnaryCall::$instances[0]->method);
        self::assertSame([
            'payload' => 'request-payload',
            'metadata' => ['request-header' => ['value']],
            'timeoutSeconds' => 1.5,
        ], UnaryCall::$instances[0]->starts[0]);
    }

    public function testUsesStatusMetadataAsUnaryTrailersWhenNativeTrailersAreEmpty(): void
    {
        UnaryCall::$nextResult = new UnaryResult(
            payload: '',
            status: new Status(14, 'unavailable', ['retry-info-bin' => ['raw']]),
        );
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $response = $bridge->unaryCall('/service.v1.Service/Method', '', [], null);

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->statusCode);
        self::assertSame('unavailable', $response->statusMessage);
        self::assertSame(['retry-info-bin' => ['raw']], $response->trailingMetadata);
    }

    public function testMapsUnknownUnaryStatusToUnknown(): void
    {
        UnaryCall::$nextResult = new UnaryResult('', new Status(99, 'unexpected'));
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $response = $bridge->unaryCall('/service.v1.Service/Method', '', [], null);

        self::assertSame(GrpcStatusCode::UNKNOWN, $response->statusCode);
        self::assertSame('unexpected', $response->statusMessage);
    }

    public function testMapsServerStreamingCallToNativeSurface(): void
    {
        ServerStreamingCall::$nextResponses = ['first', 'second'];
        ServerStreamingCall::$nextInitialMetadata = ['content-type' => ['application/grpc']];
        ServerStreamingCall::$nextTrailingMetadata = ['grpc-status' => ['0']];
        ServerStreamingCall::$nextStatus = new Status(0);
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $call = $bridge->serverStreamingCall(
            path: '/service.v1.Service/List',
            payload: 'request-payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 2.5,
        );

        self::assertInstanceOf(FrankenGrpcNativeServerStreamingCall::class, $call);
        self::assertSame(['first', 'second'], iterator_to_array($call->responses()));
        self::assertSame(GrpcStatusCode::OK, $call->statusCode());
        self::assertSame('', $call->statusMessage());
        self::assertSame(['content-type' => ['application/grpc']], $call->metadata());
        self::assertSame(['grpc-status' => ['0']], $call->trailingMetadata());
        self::assertSame('localhost:50051', $call->getPeer());
        self::assertSame('/service.v1.Service/List', ServerStreamingCall::$instances[0]->method);
        self::assertSame([
            'payload' => 'request-payload',
            'metadata' => ['request-header' => ['value']],
            'timeoutSeconds' => 2.5,
        ], ServerStreamingCall::$instances[0]->starts[0]);

        $call->cancel();
        self::assertTrue(ServerStreamingCall::$instances[0]->cancelled);
    }

    public function testUsesStatusMetadataAsServerStreamingTrailersWhenNativeTrailersAreEmpty(): void
    {
        ServerStreamingCall::$nextStatus = new Status(14, 'unavailable', ['retry-info-bin' => ['raw']]);
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $call = $bridge->serverStreamingCall('/service.v1.Service/List', '', [], null);

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $call->statusCode());
        self::assertSame('unavailable', $call->statusMessage());
        self::assertSame(['retry-info-bin' => ['raw']], $call->trailingMetadata());
    }

    public function testMapsUnknownServerStreamingStatusToUnknown(): void
    {
        ServerStreamingCall::$nextStatus = new Status(99, 'unexpected');
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $call = $bridge->serverStreamingCall('/service.v1.Service/List', '', [], null);

        self::assertSame(GrpcStatusCode::UNKNOWN, $call->statusCode());
        self::assertSame('unexpected', $call->statusMessage());
    }

    public function testCloseClosesNativeChannel(): void
    {
        $bridge = new FrankenGrpcNativeBridge('localhost:50051');

        $bridge->close();

        self::assertTrue(Channel::$instances[0]->closed);
    }
}
