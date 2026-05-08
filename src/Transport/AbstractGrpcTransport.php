<?php

declare(strict_types=1);

namespace GrpcLiteGax\Transport;

use Google\ApiCore\ApiException;
use Google\ApiCore\BidiStream;
use Google\ApiCore\Call;
use Google\ApiCore\ClientStream;
use Google\ApiCore\ServerStream;
use Google\ApiCore\Transport\TransportInterface;
use Google\ApiCore\ValidationException;
use Google\Protobuf\Internal\Message;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

abstract class AbstractGrpcTransport implements TransportInterface
{
    public function __construct(
        private readonly UnaryBackend $backend,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    #[\Override]
    public function startBidiStreamingCall(Call $call, array $options): BidiStream
    {
        throw new \BadMethodCallException('Bidi streaming calls are not supported by this transport.');
    }

    /**
     * @param array<mixed> $options
     */
    #[\Override]
    public function startClientStreamingCall(Call $call, array $options): ClientStream
    {
        throw new \BadMethodCallException('Client streaming calls are not supported by this transport.');
    }

    /**
     * @param array<mixed> $options
     */
    #[\Override]
    public function startServerStreamingCall(Call $call, array $options): ServerStream
    {
        throw new \BadMethodCallException('Server streaming calls are not supported by this transport.');
    }

    /**
     * @param array<mixed> $options
     */
    #[\Override]
    public function startUnaryCall(Call $call, array $options): PromiseInterface
    {
        $request = $this->buildUnaryRequest($call, $options);
        $promise = null;

        $promise = new Promise(function () use (&$promise, $call, $options, $request): void {
            \assert($promise instanceof Promise);

            try {
                $response = $this->backend->call($request);
                $promise->resolve($this->resolveUnaryResponse($call, $options, $response));
            } catch (\Throwable $exception) {
                $promise->reject($exception);
            }
        });

        return $promise;
    }

    #[\Override]
    public function close(): void
    {
        $this->backend->close();
    }

    /**
     * @param array<mixed> $options
     */
    private function buildUnaryRequest(Call $call, array $options): UnaryRequest
    {
        [$service, $method] = $this->splitMethod($call->getMethod());
        $message = $call->getMessage();

        if (!$message instanceof Message) {
            throw new ValidationException('Unary calls require a protobuf request message.');
        }

        return new UnaryRequest(
            service: $service,
            method: $method,
            payload: $message->serializeToString(),
            metadata: $this->normalizeMetadata($options['headers'] ?? []),
            timeoutSeconds: $this->timeoutSeconds($options['timeoutMillis'] ?? null),
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitMethod(string $method): array
    {
        $parts = explode('/', $method, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new ValidationException('Unary call method must be formatted as "service/method".');
        }

        return [$parts[0], $parts[1]];
    }

    /**
     * @param mixed $headers
     * @return array<string, list<string>>
     */
    private function normalizeMetadata(mixed $headers): array
    {
        if (!is_array($headers)) {
            throw new ValidationException('The "headers" option must be an array.');
        }

        $metadata = [];
        foreach ($headers as $name => $values) {
            if (is_string($values)) {
                $values = [$values];
            }

            if (!is_string($name) || !is_array($values)) {
                throw new ValidationException('Headers must be an array<string, string|list<string>>.');
            }

            $normalizedName = strtolower($name);
            $metadata[$normalizedName] = array_merge(
                $metadata[$normalizedName] ?? [],
                $this->normalizeMetadataValues($values),
            );
        }

        return $metadata;
    }

    /**
     * @param array<mixed> $values
     * @return list<string>
     */
    private function normalizeMetadataValues(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                throw new ValidationException('Headers must be an array<string, string|list<string>>.');
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    private function timeoutSeconds(mixed $timeoutMillis): ?float
    {
        if ($timeoutMillis === null) {
            return null;
        }

        if (!is_int($timeoutMillis) && !is_float($timeoutMillis)) {
            throw new ValidationException('The "timeoutMillis" option must be numeric.');
        }

        return $timeoutMillis / 1000;
    }

    /**
     * @param array<mixed> $options
     */
    private function resolveUnaryResponse(Call $call, array $options, UnaryResponse $response): Message
    {
        if ($response->grpcStatusCode !== GrpcStatusCode::OK) {
            throw ApiException::createFromApiResponse(
                $response->statusMessage,
                $response->grpcStatusCode->value,
                $response->metadata,
            );
        }

        $decodeType = $call->getDecodeType();
        if (!is_subclass_of($decodeType, Message::class)) {
            throw new ValidationException('Unary calls require a protobuf response decode type.');
        }

        /** @var Message $message */
        $message = new $decodeType();
        $message->mergeFromString($response->payload);

        if (isset($options['metadataCallback'])) {
            $metadataCallback = $options['metadataCallback'];
            if (!is_callable($metadataCallback)) {
                throw new ValidationException('The "metadataCallback" option must be callable.');
            }

            $metadataCallback($response->metadata);
        }

        return $message;
    }
}
