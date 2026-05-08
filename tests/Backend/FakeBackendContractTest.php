<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Support\FakeBackend;

final class FakeBackendContractTest extends UnaryBackendContractTestCase
{
    #[\Override]
    protected function createBackendForOkUnary(UnaryResponse $response): UnaryBackend
    {
        return $this->fakeBackendWith($response);
    }

    #[\Override]
    protected function createBackendForStatus(UnaryResponse $response): UnaryBackend
    {
        return $this->fakeBackendWith($response);
    }

    #[\Override]
    protected function createBackendForLifecycle(): UnaryBackend
    {
        return new FakeBackend();
    }

    private function fakeBackendWith(UnaryResponse $response): FakeBackend
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse($response);

        return $backend;
    }
}
