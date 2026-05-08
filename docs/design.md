# Design

## Goal

This package provides a PHP Composer library that adapts `google/gax` transport calls to lightweight gRPC backends. The first supported path is unary RPC. Streaming methods remain explicit non-goals until a backend contract for streaming is designed.

## Layering

The dependency direction is:

```text
Google\ApiCore\Transport\TransportInterface
  -> AbstractGrpcTransport
  -> UnaryBackend
  -> concrete backend
```

`AbstractGrpcTransport` owns the GAX-facing contract. It converts a GAX `Call` and call options into a backend `UnaryRequest`, delegates execution to `UnaryBackend`, and converts the backend `UnaryResponse` back into the promise-based GAX transport result.

`UnaryBackend` owns only unary request execution. Backend implementations must not depend on GAX client internals. Planned implementations are:

- `FrankenGrpcBackend`: FrankenPHP grpc-go bridge.
- `GrpcLiteBackend`: `php-grpc-lite` / nghttp2 bridge.
- `FakeBackend`: repository test double under `tests/Support`.

## Unary Model

`UnaryRequest` contains the canonical service name, method name, serialized protobuf payload, request metadata, and optional timeout in seconds. It can derive the gRPC path as `/{service}/{method}`.

`UnaryResponse` contains the serialized protobuf response payload, canonical gRPC status, status message, and response metadata. Successful responses are decoded by `AbstractGrpcTransport`; non-OK responses are mapped to `Google\ApiCore\ApiException`.

`AbstractGrpcTransport::close()` delegates lifecycle cleanup to the backend. Per-call cancellation is not part of the current unary backend contract; it should be designed when a concrete backend can expose cancellable in-flight calls consistently.

## Validation Boundary

Shared value objects validate backend-facing invariants early: non-empty service/method names, valid metadata shape, positive timeouts, and canonical gRPC status values. Concrete backends may add protocol-specific validation, but they should not redefine these shared invariants.

## Current Scope

The current implementation slice includes Composer package setup, PHPStan level max, PHPUnit, `AbstractGrpcTransport`, `UnaryBackend`, `FakeBackend`, and tests around the fake-backed transport path.
