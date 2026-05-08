<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend\GrpcLite;

use GrpcLiteGax\Backend\GrpcLite\GrpcLiteBackend;
use GrpcLiteGax\Backend\GrpcLite\GrpcLiteResponse;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Backend\UnaryBackendContractTestCase;
use GrpcLiteGax\Tests\Support\FakeGrpcLiteBridge;

final class GrpcLiteBackendContractTest extends UnaryBackendContractTestCase
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
        return new GrpcLiteBackend(new FakeGrpcLiteBridge());
    }

    private function backendWith(UnaryResponse $response): GrpcLiteBackend
    {
        $bridge = new FakeGrpcLiteBridge();
        $bridge->enqueueResponse(new GrpcLiteResponse(
            payload: $response->payload,
            statusCode: $response->grpcStatusCode,
            statusMessage: $response->statusMessage,
            metadata: $response->metadata,
        ));

        return new GrpcLiteBackend($bridge);
    }
}
