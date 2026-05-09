<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Transport;

use Google\ApiCore\ValidationException;
use Google\Protobuf\StringValue;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Tests\Support\FakeServerStreamingCall;
use GrpcLiteGax\Backend\ServerStreamingCall;
use GrpcLiteGax\Transport\BackendServerStreamingCall;
use PHPUnit\Framework\TestCase;

final class BackendServerStreamingCallTest extends TestCase
{
    public function testDecodesResponsesAndExposesCallState(): void
    {
        $backendCall = new FakeServerStreamingCall(
            responses: [
                $this->stringPayload('first'),
                $this->stringPayload('second'),
            ],
            metadata: ['initial' => ['value']],
            trailingMetadata: ['trailing' => ['value']],
            peer: 'peer.example',
        );
        $call = new BackendServerStreamingCall($backendCall, StringValue::class);

        /** @var list<StringValue> $responses */
        $responses = iterator_to_array($call->responses());

        self::assertSame('first', $responses[0]->getValue());
        self::assertSame('second', $responses[1]->getValue());
        self::assertSame(['initial' => ['value']], $call->getMetadata());
        self::assertSame(['trailing' => ['value']], $call->getTrailingMetadata());
        self::assertSame('peer.example', $call->getPeer());

        $status = $call->getStatus();
        self::assertSame(GrpcStatusCode::OK->value, $status->code);
        self::assertSame('', $status->details);
        self::assertSame(['trailing' => ['value']], $status->metadata);
    }

    public function testStartIsNoopAndCancelDelegates(): void
    {
        $call = new BackendServerStreamingCall(new FakeServerStreamingCall([]), StringValue::class);

        $call->start(new StringValue(), ['metadata' => ['value']], ['timeoutMillis' => 1000]);
        $call->cancel();

        $status = $call->getStatus();
        self::assertSame(GrpcStatusCode::CANCELLED->value, $status->code);
        self::assertSame('cancelled', $status->details);
    }

    public function testRejectsPerCallCredentials(): void
    {
        $call = new BackendServerStreamingCall(new FakeServerStreamingCall([]), StringValue::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Call credentials must be provided through GAX call options.');

        $call->setCallCredentials(new \stdClass());
    }

    public function testMapsBackendResponseFailureToApiException(): void
    {
        $call = new BackendServerStreamingCall(new class implements ServerStreamingCall {
            #[\Override]
            public function responses(): iterable
            {
                throw new \RuntimeException('response failure');
            }

            #[\Override]
            public function statusCode(): GrpcStatusCode
            {
                return GrpcStatusCode::OK;
            }

            #[\Override]
            public function statusMessage(): string
            {
                return '';
            }

            #[\Override]
            public function metadata(): array
            {
                return [];
            }

            #[\Override]
            public function trailingMetadata(): array
            {
                return [];
            }

            #[\Override]
            public function getPeer(): string
            {
                return 'peer';
            }

            #[\Override]
            public function cancel(): void
            {
            }
        }, StringValue::class);

        $this->expectException(\Google\ApiCore\ApiException::class);
        $this->expectExceptionCode(GrpcStatusCode::UNAVAILABLE->value);

        iterator_to_array($call->responses());
    }

    public function testMapsBackendThrowableResponseFailureToApiException(): void
    {
        $call = new BackendServerStreamingCall(new class implements ServerStreamingCall {
            #[\Override]
            public function responses(): iterable
            {
                throw new \Error('response error');
            }

            #[\Override]
            public function statusCode(): GrpcStatusCode
            {
                return GrpcStatusCode::OK;
            }

            #[\Override]
            public function statusMessage(): string
            {
                return '';
            }

            #[\Override]
            public function metadata(): array
            {
                return [];
            }

            #[\Override]
            public function trailingMetadata(): array
            {
                return [];
            }

            #[\Override]
            public function getPeer(): string
            {
                return 'peer';
            }

            #[\Override]
            public function cancel(): void
            {
            }
        }, StringValue::class);

        $this->expectException(\Google\ApiCore\ApiException::class);
        $this->expectExceptionCode(GrpcStatusCode::UNAVAILABLE->value);

        iterator_to_array($call->responses());
    }


    public function testMapsBackendStatusFailureToUnavailableStatus(): void
    {
        $call = new BackendServerStreamingCall(new class implements ServerStreamingCall {
            #[\Override]
            public function responses(): iterable
            {
                return [];
            }

            #[\Override]
            public function statusCode(): GrpcStatusCode
            {
                throw new \RuntimeException('status failure');
            }

            #[\Override]
            public function statusMessage(): string
            {
                return '';
            }

            #[\Override]
            public function metadata(): array
            {
                return [];
            }

            #[\Override]
            public function trailingMetadata(): array
            {
                return [];
            }

            #[\Override]
            public function getPeer(): string
            {
                return 'peer';
            }

            #[\Override]
            public function cancel(): void
            {
            }
        }, StringValue::class);

        $status = $call->getStatus();

        self::assertSame(GrpcStatusCode::UNAVAILABLE->value, $status->code);
        self::assertSame('status failure', $status->details);
        self::assertSame([], $status->metadata);
    }

    private function stringPayload(string $value): string
    {
        return (new StringValue(['value' => $value]))->serializeToString();
    }
}
