<?php

declare(strict_types=1);

namespace GrpcLiteGax\Backend\GrpcLite;

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

    public function close(): void;
}
