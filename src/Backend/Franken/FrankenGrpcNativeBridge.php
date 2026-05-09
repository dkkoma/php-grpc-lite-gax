<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\Franken;

use FrankenGrpc\Channel;
use FrankenGrpc\Status;
use FrankenGrpc\UnaryCall;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingCall;

/**
 * @internal
 */
final class FrankenGrpcNativeBridge implements FrankenGrpcBridge
{
    private const CHANNEL_CLASS = 'FrankenGrpc\\Channel';
    private const UNARY_CALL_CLASS = 'FrankenGrpc\\UnaryCall';
    private const SERVER_STREAMING_CALL_CLASS = 'FrankenGrpc\\ServerStreamingCall';

    private Channel $channel;

    /**
     * @param array<string, mixed> $channelOptions
     */
    public function __construct(
        string $target,
        array $channelOptions = [],
    ) {
        $this->assertNativeSurfaceAvailable();

        $channelClass = self::CHANNEL_CLASS;
        $this->channel = new $channelClass($target, $channelOptions);
    }

    /**
     * @param array<string, list<string>> $metadata
     */
    #[\Override]
    public function unaryCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): FrankenGrpcResponse {
        $callClass = self::UNARY_CALL_CLASS;
        $call = new $callClass($this->channel, $path);
        $result = $call->start($payload, $metadata, $timeoutSeconds);

        return new FrankenGrpcResponse(
            payload: $result->payload,
            statusCode: $this->statusCode($result->status),
            statusMessage: $result->status->details,
            metadata: $result->initialMetadata,
            trailingMetadata: $result->trailingMetadata !== [] ? $result->trailingMetadata : $result->status->metadata,
        );
    }

    /**
     * @param array<string, list<string>> $metadata
     */
    #[\Override]
    public function serverStreamingCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): ServerStreamingCall {
        $callClass = self::SERVER_STREAMING_CALL_CLASS;
        $call = new $callClass($this->channel, $path);
        $call->start($payload, $metadata, $timeoutSeconds);

        return new FrankenGrpcNativeServerStreamingCall($call);
    }

    #[\Override]
    public function close(): void
    {
        $this->channel->close();
    }

    // @codeCoverageIgnoreStart
    private function assertNativeSurfaceAvailable(): void
    {
        foreach ([self::CHANNEL_CLASS, self::UNARY_CALL_CLASS, self::SERVER_STREAMING_CALL_CLASS] as $className) {
            if (!class_exists($className)) {
                throw new \RuntimeException(sprintf('%s is required for FrankenGrpcNativeBridge.', $className));
            }
        }
    }
    // @codeCoverageIgnoreEnd

    private function statusCode(Status $status): GrpcStatusCode
    {
        return GrpcStatusCode::tryFrom($status->code) ?? GrpcStatusCode::UNKNOWN;
    }
}
