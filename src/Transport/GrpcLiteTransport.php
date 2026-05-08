<?php

declare(strict_types=1);

namespace GrpcLiteGax\Transport;

use GrpcLiteGax\Backend\GrpcLite\GrpcLiteBackend;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteNativeBridge;
use GrpcLiteGax\Backend\UnaryBackend;

final class GrpcLiteTransport extends AbstractGrpcTransport
{
    private function __construct(UnaryBackend $backend)
    {
        parent::__construct($backend);
    }

    /**
     * @param array<string, mixed> $channelOptions
     */
    public static function build(string $endpoint, array $channelOptions = []): self
    {
        return new self(new GrpcLiteBackend(new GrpcLiteNativeBridge($endpoint, $channelOptions)));
    }
}
