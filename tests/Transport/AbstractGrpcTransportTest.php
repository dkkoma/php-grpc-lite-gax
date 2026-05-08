<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use Google\ApiCore\ApiException;
use Google\ApiCore\Call;
use Google\Protobuf\StringValue;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Support\FakeBackend;
use GrpcLiteGax\Tests\Support\TestGrpcTransport;
use PHPUnit\Framework\TestCase;

final class AbstractGrpcTransportTest extends TestCase
{
    public function testUnaryCallDelegatesToBackendAndDecodesResponse(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse($this->stringPayload('response-value')));

        $transport = new TestGrpcTransport($backend);
        $promise = $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [
                'headers' => ['x-goog-request-params' => ['name=example']],
                'timeoutMillis' => 1500,
            ],
        );

        $response = $promise->wait();

        self::assertInstanceOf(StringValue::class, $response);
        self::assertSame('response-value', $response->getValue());

        $request = $backend->lastRequest();
        self::assertSame('google.example.v1.ExampleService', $request->service);
        self::assertSame('GetExample', $request->method);
        self::assertSame('request-value', $this->decodeStringPayload($request->payload));
        self::assertSame(['x-goog-request-params' => ['name=example']], $request->metadata);
        self::assertSame(1.5, $request->timeoutSeconds);
    }

    public function testUnaryCallMapsNonOkBackendResponseToApiException(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse(
            payload: '',
            grpcStatusCode: GrpcStatusCode::UNAVAILABLE,
            statusMessage: 'backend unavailable',
            metadata: ['retry-info-bin' => ['raw']],
        ));

        $transport = new TestGrpcTransport($backend);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(GrpcStatusCode::UNAVAILABLE->value);

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        )->wait();
    }

    private function stringPayload(string $value): string
    {
        return (new StringValue(['value' => $value]))->serializeToString();
    }

    private function decodeStringPayload(string $payload): string
    {
        $message = new StringValue();
        $message->mergeFromString($payload);

        return $message->getValue();
    }
}
