<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use Google\ApiCore\ApiException;
use Google\ApiCore\Call;
use Google\ApiCore\ValidationException;
use Google\Protobuf\StringValue;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Support\FakeBackend;
use GrpcLiteGax\Tests\Support\TestGrpcTransport;
use GuzzleHttp\Promise\CancellationException;
use PHPUnit\Framework\TestCase;

final class AbstractGrpcTransportTest extends TestCase
{
    public function testUnaryCallDelegatesToBackendAndDecodesResponse(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse(
            payload: $this->stringPayload('response-value'),
            metadata: ['response-header' => ['response-metadata']],
        ));

        $transport = new TestGrpcTransport($backend);
        $responseMetadata = null;
        $promise = $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [
                'headers' => [
                    'Authorization' => ['Bearer token'],
                    'authorization' => ['Bearer fallback'],
                    'x-goog-request-params' => ['name=example'],
                ],
                'metadataCallback' => static function (array $metadata) use (&$responseMetadata): void {
                    $responseMetadata = $metadata;
                },
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
        self::assertSame([
            'authorization' => ['Bearer token', 'Bearer fallback'],
            'x-goog-request-params' => ['name=example'],
        ], $request->metadata);
        self::assertSame(1.5, $request->timeoutSeconds);
        self::assertSame(['response-header' => ['response-metadata']], $responseMetadata);
    }

    public function testCloseDelegatesToBackend(): void
    {
        $backend = new FakeBackend();
        $transport = new TestGrpcTransport($backend);

        $transport->close();

        self::assertTrue($backend->isClosed());
    }

    public function testCancelBeforeWaitDoesNotCallBackend(): void
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
            [],
        );

        $promise->cancel();

        self::assertSame([], $backend->requests());
        self::assertSame(1, $backend->pendingResponseCount());

        $this->expectException(CancellationException::class);

        $promise->wait();
    }

    public function testRejectsUnsupportedStreamingCalls(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());
        $call = new Call(
            method: 'google.example.v1.ExampleService/GetExample',
            decodeType: StringValue::class,
            message: new StringValue(['value' => 'request-value']),
        );

        $this->expectException(\BadMethodCallException::class);

        $transport->startServerStreamingCall($call, []);
    }

    public function testRejectsMalformedMethodName(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary call method must be formatted as "service/method".');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleServiceGetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        );
    }

    public function testRejectsNonProtobufRequestMessage(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary calls require a protobuf request message.');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: 'request-value',
            ),
            [],
        );
    }

    public function testRejectsInvalidHeaders(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "headers" option must be an array.');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            ['headers' => 'bad'],
        );
    }

    public function testRejectsInvalidTimeoutMillis(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "timeoutMillis" option must be numeric.');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            ['timeoutMillis' => 'bad'],
        );
    }

    public function testRejectsInvalidDecodeType(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse($this->stringPayload('response-value')));
        $transport = new TestGrpcTransport($backend);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary calls require a protobuf response decode type.');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/GetExample',
                decodeType: \stdClass::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        )->wait();
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
