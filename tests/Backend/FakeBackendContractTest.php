<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Support\FakeBackend;

final class FakeBackendContractTest extends UnaryBackendContractTestCase
{
    /**
     * @param list<UnaryResponse> $responses
     */
    #[\Override]
    protected function createBackend(array $responses): UnaryBackend
    {
        $backend = new FakeBackend();
        foreach ($responses as $response) {
            $backend->enqueueResponse($response);
        }

        return $backend;
    }
}
