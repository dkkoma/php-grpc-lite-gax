<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\GrpcLite;

use Grpc\Call;
use Grpc\Channel;
use Grpc\ChannelCredentials;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteNativeBridge;
use GrpcLiteGax\Backend\GrpcStatusCode;
use PHPUnit\Framework\TestCase;

use const Grpc\OP_SEND_INITIAL_METADATA;
use const Grpc\OP_SEND_MESSAGE;

final class GrpcLiteNativeBridgeTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Channel::$instances = [];
        Call::$instances = [];
        Call::$nextReceiveEvent = null;
    }

    public function testCreatesChannelWithDefaultSslCredentials(): void
    {
        new GrpcLiteNativeBridge('service.googleapis.com:443');

        self::assertCount(1, Channel::$instances);
        self::assertSame('service.googleapis.com:443', Channel::$instances[0]->target);
        self::assertInstanceOf(ChannelCredentials::class, Channel::$instances[0]->opts['credentials']);
        self::assertSame('ssl', Channel::$instances[0]->opts['credentials']->type);
    }

    public function testMapsUnaryCallToNativeCallSurface(): void
    {
        Call::$nextReceiveEvent = (object) [
            'metadata' => ['content-type' => ['application/grpc']],
            'message' => 'response-payload',
            'status' => (object) [
                'code' => 0,
                'details' => '',
                'metadata' => ['grpc-status' => ['0']],
            ],
        ];
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');

        $response = $bridge->unaryCall(
            path: '/service.v1.Service/Method',
            payload: 'request-payload',
            metadata: ['request-header' => ['value']],
            timeoutSeconds: 1.5,
        );

        self::assertSame('response-payload', $response->payload);
        self::assertSame(GrpcStatusCode::OK, $response->statusCode);
        self::assertSame([
            'content-type' => ['application/grpc'],
        ], $response->metadata);
        self::assertSame(['grpc-status' => ['0']], $response->trailingMetadata);

        self::assertCount(1, Call::$instances);
        self::assertSame('/service.v1.Service/Method', Call::$instances[0]->method);
        self::assertSame(2_500_000, Call::$instances[0]->deadline->microseconds);
        self::assertSame(['request-header' => ['value']], Call::$instances[0]->batches[0][OP_SEND_INITIAL_METADATA]);
        self::assertSame(['message' => 'request-payload'], Call::$instances[0]->batches[0][OP_SEND_MESSAGE]);
    }

    public function testMapsUnknownNativeStatusToUnknown(): void
    {
        Call::$nextReceiveEvent = (object) [
            'metadata' => [],
            'message' => '',
            'status' => (object) [
                'code' => 99,
                'details' => 'unexpected',
                'metadata' => [],
            ],
        ];
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');

        $response = $bridge->unaryCall('/service.v1.Service/Method', '', [], null);

        self::assertSame(GrpcStatusCode::UNKNOWN, $response->statusCode);
        self::assertSame('unexpected', $response->statusMessage);
        self::assertSame(PHP_INT_MAX, Call::$instances[0]->deadline->microseconds);
    }

    public function testRejectsNativeEventWithoutStatusCode(): void
    {
        Call::$nextReceiveEvent = (object) [
            'metadata' => [
                0 => ['ignored'],
                'single' => 'value',
                'bad-map' => ['nested' => 'value'],
                'bad-values' => [1],
                'empty' => [],
            ],
            'message' => null,
            'status' => (object) [
                'metadata' => 'not-metadata',
            ],
        ];
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing an integer gRPC status code');

        $bridge->unaryCall('/service.v1.Service/Method', '', [], null);
    }

    public function testNormalizesUnexpectedNativeMetadataShapes(): void
    {
        Call::$nextReceiveEvent = (object) [
            'metadata' => [
                0 => ['ignored'],
                'single' => 'value',
                'bad-map' => ['nested' => 'value'],
                'bad-values' => [1],
                'empty' => [],
            ],
            'message' => null,
            'status' => (object) [
                'code' => 0,
                'details' => '',
                'metadata' => 'not-metadata',
            ],
        ];
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');

        $response = $bridge->unaryCall('/service.v1.Service/Method', '', [], null);

        self::assertSame('', $response->payload);
        self::assertSame(GrpcStatusCode::OK, $response->statusCode);
        self::assertSame('', $response->statusMessage);
        self::assertSame(['single' => ['value']], $response->metadata);
    }

    public function testRejectsMissingGrpcOperationConstant(): void
    {
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');
        $method = new \ReflectionMethod($bridge, 'grpcConstant');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grpc\\MISSING_CONSTANT is required');

        $method->invoke($bridge, 'MISSING_CONSTANT');
    }

    public function testRejectsNonIntegerGrpcOperationConstant(): void
    {
        \defined('Grpc\\BAD_CONSTANT') || \define('Grpc\\BAD_CONSTANT', 'bad');

        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');
        $method = new \ReflectionMethod($bridge, 'grpcConstant');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grpc\\BAD_CONSTANT must be an integer');

        $method->invoke($bridge, 'BAD_CONSTANT');
    }

    public function testCloseClosesNativeChannel(): void
    {
        $bridge = new GrpcLiteNativeBridge('service.googleapis.com:443');

        $bridge->close();

        self::assertTrue(Channel::$instances[0]->closed);
    }
}
