<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\Franken;

use GrpcLiteGax\Backend\Franken\FrankenGrpcBackend;
use GrpcLiteGax\Backend\Franken\FrankenGrpcResponse;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Backend\UnaryBackendContractTestCase;
use GrpcLiteGax\Tests\Support\FakeFrankenGrpcBridge;

final class FrankenGrpcBackendContractTest extends UnaryBackendContractTestCase
{
    #[\Override]
    protected function createBackendForOkUnary(UnaryResponse $response): UnaryBackend
    {
        return $this->backendWith($response);
    }

    #[\Override]
    protected function createBackendForStatus(UnaryResponse $response): UnaryBackend
    {
        return $this->backendWith($response);
    }

    #[\Override]
    protected function createBackendForLifecycle(): UnaryBackend
    {
        return new FrankenGrpcBackend(new FakeFrankenGrpcBridge());
    }

    private function backendWith(UnaryResponse $response): FrankenGrpcBackend
    {
        $bridge = new FakeFrankenGrpcBridge();
        $bridge->enqueueResponse(new FrankenGrpcResponse(
            payload: $response->payload,
            statusCode: $response->grpcStatusCode,
            statusMessage: $response->statusMessage,
            metadata: $response->metadata,
            trailingMetadata: $response->trailingMetadata,
        ));

        return new FrankenGrpcBackend($bridge);
    }
}
