<?php

declare(strict_types=1);

namespace GrpcLiteGax\Transport;

use Google\ApiCore\ApiException;
use Google\ApiCore\BidiStream;
use Google\ApiCore\Call;
use Google\ApiCore\ClientStream;
use Google\ApiCore\HeaderCredentialsInterface;
use Google\ApiCore\ServerStream;
use Google\ApiCore\Transport\TransportInterface;
use Google\ApiCore\ValidationException;
use Google\Protobuf\Internal\Message;
use GrpcLiteGax\Backend\GrpcStatusCode;
use GrpcLiteGax\Backend\ServerStreamingBackend;
use GrpcLiteGax\Backend\ServerStreamingRequest;
use GrpcLiteGax\Backend\UnaryBackend;
use GrpcLiteGax\Backend\UnaryRequest;
use GrpcLiteGax\Backend\UnaryResponse;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * @internal
 */
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
        if (!$this->backend instanceof ServerStreamingBackend) {
            throw new \BadMethodCallException('Server streaming calls are not supported by this backend.');
        }

        $request = $this->buildServerStreamingRequest($call, $options);
        $decodeType = $call->getDecodeType();
        if (!is_subclass_of($decodeType, Message::class)) {
            throw new ValidationException('Server streaming calls require a protobuf response decode type.');
        }

        try {
            $backendCall = $this->backend->start($request);
        } catch (\Throwable $exception) {
            throw $this->backendFailure($exception);
        }

        return new ServerStream(
            new BackendServerStreamingCall($backendCall, $decodeType),
            $call->getDescriptor() ?? [],
        );
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
            } catch (\Throwable $exception) {
                $promise->reject($this->backendFailure($exception));
                return;
            }

            try {
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
            metadata: $this->normalizeMetadata($this->headersWithCredentials($options)),
            timeoutSeconds: $this->timeoutSeconds($options['timeoutMillis'] ?? null),
        );
    }

    /**
     * @param array<mixed> $options
     */
    private function buildServerStreamingRequest(Call $call, array $options): ServerStreamingRequest
    {
        [$service, $method] = $this->splitMethod($call->getMethod());
        $message = $call->getMessage();

        if (!$message instanceof Message) {
            throw new ValidationException('Server streaming calls require a protobuf request message.');
        }

        return new ServerStreamingRequest(
            service: $service,
            method: $method,
            payload: $message->serializeToString(),
            metadata: $this->normalizeMetadata($this->headersWithCredentials($options)),
            timeoutSeconds: $this->timeoutSeconds($options['timeoutMillis'] ?? null),
        );
    }

    /**
     * @param array<mixed> $options
     * @return array<mixed>
     */
    private function headersWithCredentials(array $options): array
    {
        $headers = $options['headers'] ?? [];

        if (!is_array($headers)) {
            throw new ValidationException('The "headers" option must be an array.');
        }

        if (isset($options['credentialsWrapper'])) {
            $credentialsWrapper = $options['credentialsWrapper'];
            if (!$credentialsWrapper instanceof HeaderCredentialsInterface) {
                throw new ValidationException(
                    'The "credentialsWrapper" option must implement HeaderCredentialsInterface.',
                );
            }

            $credentialsWrapper->checkUniverseDomain();
        }

        if (isset($credentialsWrapper) && !$this->hasAuthorizationHeader($headers)) {
            $audience = $options['audience'] ?? null;
            if ($audience !== null && !is_string($audience)) {
                throw new ValidationException('The "audience" option must be a string.');
            }

            $callback = $credentialsWrapper->getAuthorizationHeaderCallback($audience);
            $authHeaders = $callback === null ? [] : $callback();
            if (!is_array($authHeaders)) {
                throw new \UnexpectedValueException('Expected array response from authorization header callback.');
            }

            $headers += $authHeaders;
        }

        return $headers;
    }

    /**
     * @param array<mixed> $headers
     */
    private function hasAuthorizationHeader(array $headers): bool
    {
        foreach ($headers as $name => $_) {
            if (is_string($name) && strtolower($name) === 'authorization') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitMethod(string $method): array
    {
        if (substr_count($method, '/') !== 1) {
            throw new ValidationException('Unary call method must be formatted as "service/method".');
        }

        $parts = explode('/', $method, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new ValidationException('Unary call method must be formatted as "service/method".');
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $parts[0])) {
            throw new ValidationException('Unary call service must be a canonical protobuf service name.');
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $parts[1])) {
            throw new ValidationException('Unary call method must be a protobuf method name.');
        }

        return [$parts[0], $parts[1]];
    }

    /**
     * @param array<mixed> $headers
     * @return array<string, list<string>>
     */
    private function normalizeMetadata(array $headers): array
    {
        $metadata = [];
        foreach ($headers as $name => $values) {
            if (is_string($values)) {
                $values = [$values];
            }

            if (!is_string($name) || !is_array($values)) {
                throw new ValidationException('Headers must be an array<string, string|list<string>>.');
            }

            if (!preg_match('/^[0-9a-z_.-]+$/', strtolower($name))) {
                throw new ValidationException('Header names must use gRPC metadata characters.');
            }

            if (str_starts_with(strtolower($name), 'grpc-')) {
                throw new ValidationException('Header names starting with grpc- are reserved.');
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
        if (!array_is_list($values)) {
            throw new ValidationException('Header values must be lists of strings.');
        }

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

        if (!is_finite((float) $timeoutMillis) || $timeoutMillis <= 0) {
            throw new ValidationException('The "timeoutMillis" option must be finite and positive.');
        }

        return $timeoutMillis / 1000;
    }

    private function backendFailure(\Throwable $exception): ApiException
    {
        $previous = $exception instanceof \Exception
            ? $exception
            : new \RuntimeException($exception->getMessage(), (int) $exception->getCode(), $exception);

        return ApiException::createFromApiResponse(
            $exception->getMessage(),
            GrpcStatusCode::UNAVAILABLE->value,
            previous: $previous,
        );
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
                $response->trailingMetadata !== [] ? $response->trailingMetadata : $response->metadata,
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
