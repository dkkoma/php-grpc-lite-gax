<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use Google\ApiCore\ApiException;
use Google\ApiCore\Call;
use Google\ApiCore\ValidationException;
use Google\Protobuf\StringValue;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryResponse;
use GrpcLiteGax\Tests\Fixtures\GaxUnaryCallFixture;
use GrpcLiteGax\Tests\Support\FakeBackend;
use GrpcLiteGax\Tests\Support\TestGrpcTransport;
use GrpcLiteGax\Tests\Support\ThrowingBackend;
use GuzzleHttp\Promise\CancellationException;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $options = GaxUnaryCallFixture::options();
        $options['headers']['Authorization'] = ['Bearer token'];
        $options['headers']['authorization'] = ['Bearer fallback'];
        $options['metadataCallback'] = static function (array $metadata) use (&$responseMetadata): void {
            $responseMetadata = $metadata;
        };

        $promise = $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            $options,
        );

        $response = $promise->wait();

        self::assertInstanceOf(StringValue::class, $response);
        self::assertSame('response-value', $response->getValue());

        $request = $backend->lastRequest();
        self::assertSame('google.example.v1.ExampleService', $request->service);
        self::assertSame('GetExample', $request->method);
        self::assertSame('request-value', $this->decodeStringPayload($request->payload));
        self::assertSame([
            'x-goog-request-params' => ['name=projects/example/locations/global'],
            'x-goog-api-client' => ['gapic/0.0.0 gax/' . \Google\ApiCore\Version::getApiCoreVersion()],
            'authorization' => ['Bearer token', 'Bearer fallback'],
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
            GaxUnaryCallFixture::call(),
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

        $this->expectException(\BadMethodCallException::class);

        $transport->startServerStreamingCall(GaxUnaryCallFixture::call(), []);
    }

    public function testRejectsUnsupportedClientStreamingCalls(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(\BadMethodCallException::class);

        $transport->startClientStreamingCall(GaxUnaryCallFixture::call(), []);
    }

    public function testRejectsUnsupportedBidiStreamingCalls(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(\BadMethodCallException::class);

        $transport->startBidiStreamingCall(GaxUnaryCallFixture::call(), []);
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

    public function testRejectsMethodNameWithExtraSlash(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary call method must be formatted as "service/method".');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/Bad/Method',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        );
    }

    public function testRejectsInvalidServiceToken(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary call service must be a canonical protobuf service name.');

        $transport->startUnaryCall(
            new Call(
                method: '1bad.Service/Method',
                decodeType: StringValue::class,
                message: new StringValue(['value' => 'request-value']),
            ),
            [],
        );
    }

    public function testRejectsInvalidMethodToken(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unary call method must be a protobuf method name.');

        $transport->startUnaryCall(
            new Call(
                method: 'google.example.v1.ExampleService/1BadMethod',
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
            GaxUnaryCallFixture::call(),
            ['headers' => 'bad'],
        );
    }

    public function testRejectsInvalidHeaderValues(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Headers must be an array<string, string|list<string>>.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['headers' => ['metadata' => [1]]],
        );
    }

    public function testRejectsAssociativeHeaderValueArrays(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Header values must be lists of strings.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['headers' => ['metadata' => ['key' => 'value']]],
        );
    }

    public function testRejectsInvalidHeaderNames(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Header names must use gRPC metadata characters.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['headers' => ['bad/header' => ['value']]],
        );
    }

    public function testRejectsReservedHeaderNames(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Header names starting with grpc- are reserved.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['headers' => ['grpc-timeout' => ['1S']]],
        );
    }

    public function testRejectsInvalidTimeoutMillis(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "timeoutMillis" option must be numeric.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['timeoutMillis' => 'bad'],
        );
    }

    public function testRejectsNonPositiveTimeoutMillis(): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "timeoutMillis" option must be finite and positive.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['timeoutMillis' => 0],
        );
    }

    /**
     * @return iterable<string, array{float}>
     */
    public static function nonFiniteTimeoutMillisProvider(): iterable
    {
        yield 'infinity' => [INF];
        yield 'negative infinity' => [-INF];
        yield 'not a number' => [NAN];
    }

    #[DataProvider('nonFiniteTimeoutMillisProvider')]
    public function testRejectsNonFiniteTimeoutMillis(float $timeoutMillis): void
    {
        $transport = new TestGrpcTransport(new FakeBackend());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "timeoutMillis" option must be finite and positive.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['timeoutMillis' => $timeoutMillis],
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
            GaxUnaryCallFixture::call(),
            [],
        )->wait();
    }

    public function testUnaryCallMapsBackendThrowableToUnavailableApiException(): void
    {
        $transport = new TestGrpcTransport(new ThrowingBackend(new \RuntimeException('network down')));

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(GrpcStatusCode::UNAVAILABLE->value);

        $transport->startUnaryCall(GaxUnaryCallFixture::call(), [])->wait();
    }

    public function testRejectsInvalidMetadataCallback(): void
    {
        $backend = new FakeBackend();
        $backend->enqueueResponse(new UnaryResponse($this->stringPayload('response-value')));
        $transport = new TestGrpcTransport($backend);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The "metadataCallback" option must be callable.');

        $transport->startUnaryCall(
            GaxUnaryCallFixture::call(),
            ['metadataCallback' => 'not-a-callable'],
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
