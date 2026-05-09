# FrankenPHP Extension API Specification

## Purpose

This document defines the PHP extension surface required for a FrankenPHP grpc-go backend that can adapt to this repository's GAX transport contracts. The extension should expose byte-oriented gRPC primitives. It must not depend on `google/gax`, google-cloud-php clients, or generated protobuf classes.

## Scope

Required call types:

- Unary RPC
- Server streaming RPC

Out of scope:

- Client streaming
- Bidirectional streaming
- Protobuf message encoding or decoding
- GAX retry logic

## Data Model

All request and response messages are serialized protobuf bytes.

Metadata shape is:

```php
array<string, list<string>>
```

Header names should be lowercase gRPC metadata keys. Binary metadata keys must end in `-bin` and carry raw binary string values. Reserved `grpc-*` request metadata must be rejected by the PHP adapter before the extension boundary.

Status uses canonical gRPC integer codes. Status details are a human-readable string. Trailing metadata must include status-detail trailers, such as `grpc-status-details-bin`, when grpc-go exposes them.

## Required PHP Surface

```php
namespace FrankenGrpc;

final class Channel
{
    public function __construct(string $target, array $options = []);
    public function close(): void;
}

final class UnaryCall
{
    public function __construct(Channel $channel, string $method);

    public function start(
        string $payload,
        array $metadata = [],
        ?float $timeoutSeconds = null,
    ): UnaryResult;

    public function cancel(): void;
    public function getPeer(): string;
}

final class ServerStreamingCall
{
    public function __construct(Channel $channel, string $method);

    public function start(
        string $payload,
        array $metadata = [],
        ?float $timeoutSeconds = null,
    ): void;

    public function read(): ?string;
    public function getInitialMetadata(): array;
    public function getStatus(): Status;
    public function getTrailingMetadata(): array;
    public function cancel(): void;
    public function getPeer(): string;
}

final readonly class UnaryResult
{
    public function __construct(
        public string $payload,
        public Status $status,
        public array $initialMetadata = [],
        public array $trailingMetadata = [],
    ) {}
}

final readonly class Status
{
    public function __construct(
        public int $code,
        public string $details = '',
        public array $metadata = [],
    ) {}
}
```

## Method Paths

The method string is the fully qualified gRPC path:

```text
/package.Service/Method
```

Example:

```text
/google.pubsub.v1.Publisher/Publish
```

The extension should treat the method as opaque except for passing it to grpc-go.

## Deadlines

`timeoutSeconds` is a relative timeout from call start. `null` means no explicit deadline. The extension should convert non-null values into grpc-go context deadlines. If the deadline expires, return canonical `DEADLINE_EXCEEDED` status when grpc-go provides it.

## Unary Semantics

Unary `start()` must:

1. Send initial metadata.
2. Send exactly one request message.
3. Half-close the client side.
4. Receive initial metadata, one response message, trailing metadata, and final status.
5. Return `UnaryResult`.

If the server returns a non-OK final status with no response payload, `payload` may be an empty string. The status and trailing metadata must still be returned.

## Server Streaming Semantics

Server streaming `start()` must:

1. Send initial metadata.
2. Send exactly one request message.
3. Half-close the client side.
4. Make initial metadata available through `getInitialMetadata()`.

`read()` returns the next serialized response payload, or `null` at end of stream. After `read()` returns `null`, `getStatus()` must return the final gRPC status and `getTrailingMetadata()` must return trailers.

Calling `getInitialMetadata()` before `read()` must not consume or drop the first response message. Repeated `getInitialMetadata()`, `getStatus()`, and `getTrailingMetadata()` calls should be idempotent and return cached values.

## Cancellation And Lifecycle

`cancel()` should cancel the in-flight grpc-go context. If cancellation wins before final status is available, the final status should be `CANCELLED` when grpc-go exposes that status.

`Channel::close()` must be idempotent. Calls started after channel close should fail predictably with a PHP exception. Calls already in flight may complete or fail according to grpc-go behavior, but must not crash PHP.

## Error Semantics

Protocol-level failures with a gRPC status should be surfaced as `Status`.

Transport failures before any gRPC status is available should throw a PHP exception. The adapter in this repository maps those exceptions to GAX `UNAVAILABLE`.

Malformed native state, such as a missing integer status code, should throw a PHP exception rather than manufacturing an OK response.

## Compliance Expectations

The extension repository should provide tests or examples covering:

- Unary success with initial and trailing metadata.
- Unary non-OK status with status details metadata.
- Server streaming success with multiple messages.
- Server streaming non-OK final status after at least one message.
- `getInitialMetadata()` before `read()`.
- Deadline exceeded.
- Cancellation.
- Channel close idempotency.
- Binary metadata preservation.

This repository should then add a FrankenPHP bridge smoke suite equivalent to the current `php-grpc-lite` native, Pub/Sub emulator, and Spanner emulator smoke paths.
