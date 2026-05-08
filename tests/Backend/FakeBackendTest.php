<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Backend;

use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Support\FakeBackend;
use PHPUnit\Framework\TestCase;

final class FakeBackendTest extends TestCase
{
    public function testReturnsQueuedUnaryResponseAndRecordsRequest(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse(
            payload: 'response-bytes',
            metadata: ['request-id' => ['abc-123']],
        ));

        $request = new UnaryRequest(
            service: 'google.example.v1.ExampleService',
            method: 'GetExample',
            payload: 'request-bytes',
            metadata: ['authorization' => ['Bearer token']],
            timeoutSeconds: 5.0,
        );

        $response = $backend->call($request);

        self::assertSame('response-bytes', $response->payload);
        self::assertTrue($response->isOk());
        self::assertSame(['request-id' => ['abc-123']], $response->metadata);
        self::assertSame([$request], $backend->requests());
        self::assertSame($request, $backend->lastRequest());
        self::assertSame('/google.example.v1.ExampleService/GetExample', $request->path());
    }

    public function testQueuedResponsesAreReturnedInOrder(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse('first'));
        $backend->enqueueResponse(new UnaryResponse('second'));

        $request = new UnaryRequest('service.v1.Service', 'Method', 'payload');

        self::assertSame('first', $backend->call($request)->payload);
        self::assertSame('second', $backend->call($request)->payload);
        self::assertCount(2, $backend->requests());
        self::assertSame(0, $backend->pendingResponseCount());
        $backend->assertNoPendingResponses();
    }

    public function testPreservesNonOkResponse(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse('', GrpcStatusCode::UNAVAILABLE, 'unavailable'));

        $response = $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'payload'));

        self::assertSame(GrpcStatusCode::UNAVAILABLE, $response->grpcStatusCode);
        self::assertSame('unavailable', $response->statusMessage);
    }

    public function testFailsWhenResponsesRemainPending(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse('pending'));

        self::assertSame(1, $backend->pendingResponseCount());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FakeBackend has pending unary responses.');

        $backend->assertNoPendingResponses();
    }

    public function testFailsWhenNoResponseIsQueued(): void
    {
        $backend = new FakeBackend();

        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('FakeBackend has no queued unary response.');

        $backend->call(new UnaryRequest('service.v1.Service', 'Method', 'payload'));
    }

}
