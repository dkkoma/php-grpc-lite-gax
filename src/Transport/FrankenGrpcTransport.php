<?php

declare(strict_types=1);

namespace GrpcLiteGax\Transport;

use GrpcLiteGax\Backend\Franken\FrankenGrpcBackend;
use GrpcLiteGax\Backend\Franken\FrankenGrpcNativeBridge;
use GrpcLiteGax\Backend\UnaryBackend;

final class FrankenGrpcTransport extends AbstractGrpcTransport
{
    private function __construct(UnaryBackend $backend)
    {
        parent::__construct($backend);
    }

    /**
     * @param array<string, mixed> $channelOptions
     */
    public static function build(string $target, array $channelOptions = []): self
    {
        return new self(new FrankenGrpcBackend(new FrankenGrpcNativeBridge($target, $channelOptions)));
    }
}
