<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\GrpcLite;

use GrpcLiteGax\Backend\ServerStreamingCall;

/**
 * @internal
 */
interface GrpcLiteBridge
{
    /**
     * @param array<string, list<string>> $metadata
     */
    public function unaryCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): GrpcLiteResponse;

    /**
     * @param array<string, list<string>> $metadata
     */
    public function serverStreamingCall(
        string $path,
        string $payload,
        array $metadata,
        ?float $timeoutSeconds,
    ): ServerStreamingCall;

    public function close(): void;
}
