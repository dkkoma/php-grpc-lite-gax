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

`UnaryBackend::close()` is idempotent. After close, `call()` must fail predictably with `BackendClosedException`. Backend transport failures that do not produce a gRPC status may throw; `AbstractGrpcTransport` owns mapping those failures to GAX `ApiException` with `GrpcStatusCode::UNAVAILABLE`.

For the current unary slice, `UnaryResponse::metadata` is a single metadata bag used for successful response metadata callbacks and non-OK error metadata. Concrete backend design may split initial metadata and trailing metadata later if grpc-go or `php-grpc-lite` mapping requires that distinction.

## FrankenGrpcBackend

`FrankenGrpcBackend` is the FrankenPHP bridge to grpc-go. It depends only on `UnaryBackend` inputs and outputs, not on GAX `Call` objects. Request mapping sends `UnaryRequest::path()` as the fully qualified gRPC method path, `payload` as the serialized protobuf request body, metadata as lowercase gRPC metadata, and `timeoutSeconds` as a relative duration from which the backend derives the grpc-go deadline or context timeout.

Response mapping converts grpc-go response bytes into `UnaryResponse::payload`, trailers/headers into response metadata, and grpc-go canonical status into `GrpcStatusCode` plus status message. Transport-level failures that do not produce a gRPC status should map to `GrpcStatusCode::UNAVAILABLE` unless a more precise canonical status is available.

The backend owns grpc-go channel/client lifecycle. `close()` must release backend resources, be safe to call more than once, and make later calls fail predictably. Per-call cancellation remains outside the current contract.

The PHP implementation uses an internal bridge interface so grpc-go bindings remain replaceable. The bridge accepts backend-native scalar data: path, payload, metadata, and timeout duration. `FrankenGrpcBackend` owns validation of the bridge result and conversion into `UnaryResponse`.

## GrpcLiteBackend

`GrpcLiteBackend` is the `php-grpc-lite` / nghttp2 backend. It must share the same `UnaryBackend` contract as `FrankenGrpcBackend`: no GAX `Call` dependency, one unary request in, one unary response or backend exception out.

Request mapping sends `UnaryRequest::path()` as the HTTP/2 `:path`, uses POST semantics, sends the serialized protobuf `payload` as the gRPC request message body, forwards lowercase metadata as gRPC metadata headers, and treats `timeoutSeconds` as a relative timeout duration for the nghttp2 request. The backend is responsible for any gRPC wire framing required by `php-grpc-lite`.

Response mapping extracts the response message bytes into `UnaryResponse::payload`, maps response headers/trailers into the current single response metadata bag, and converts the gRPC status trailer into `GrpcStatusCode` plus status message. If nghttp2 or `php-grpc-lite` fails before a gRPC status is available, the backend may throw and let `AbstractGrpcTransport` map the failure to `UNAVAILABLE`.

`GrpcLiteBackend::close()` should release client/session resources, be idempotent, and make later calls fail with `BackendClosedException`.

## Validation Boundary

Shared value objects validate backend-facing invariants early: non-empty service/method names, valid metadata shape, positive timeouts, and canonical gRPC status values. Concrete backends may add protocol-specific validation, but they should not redefine these shared invariants.

## Public Boundary

The current backend and abstract transport types are internal implementation contracts. `UnaryBackend`, `UnaryRequest`, `UnaryResponse`, `GrpcStatusCode`, and `AbstractGrpcTransport` may change while the first concrete backends are being designed. Public stable APIs should be introduced around concrete transports or factories after the FrankenPHP and `php-grpc-lite` backend contracts have proven compatible.

## Current Scope

The current implementation slice includes Composer package setup, PHPStan level max, PHPUnit, `AbstractGrpcTransport`, `UnaryBackend`, `FakeBackend`, and tests around the fake-backed transport path.
