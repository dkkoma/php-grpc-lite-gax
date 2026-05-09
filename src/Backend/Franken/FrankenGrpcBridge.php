<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\Franken;

/**
 * @internal
 */
interface FrankenGrpcBridge
{
    /**
     * @param array<string, list<string>> $metadata
     */
    public function unaryCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): FrankenGrpcResponse;

    /**
     * @param array<string, list<string>> $metadata
     */
    public function serverStreamingCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): \GrpcLiteGax\Backend\ServerStreamingCall;

    public function close(): void;
}
