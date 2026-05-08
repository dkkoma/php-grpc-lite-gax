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

`AbstractGrpcTransport` owns the GAX-facing contract. It converts a GAX `Call` and call options into a backend `UnaryRequest`, delegates execution to `UnaryBackend`, and converts the backend `UnaryResponse` back into the promise-based GAX transport result. It also normalizes GAX credential callbacks into request metadata before the backend boundary.

`UnaryBackend` owns only unary request execution. Backend implementations must not depend on GAX client internals. Current implementations are:

- Current: `FrankenGrpcBackend`, the FrankenPHP grpc-go bridge.
- Current: `FakeBackend`, the repository test double under `tests/Support`.
- Current: `GrpcLiteBackend`, the `php-grpc-lite` / nghttp2 bridge.

## Unary Model

`UnaryRequest` contains the canonical service name, method name, serialized protobuf payload, request metadata, and optional timeout in seconds. It can derive the gRPC path as `/{service}/{method}`.

`UnaryResponse` contains the serialized protobuf response payload, canonical gRPC status, status message, initial response metadata, and trailing/status metadata. Successful responses are decoded by `AbstractGrpcTransport` and expose only initial metadata to `metadataCallback`; non-OK responses are mapped to `Google\ApiCore\ApiException` with trailing/status metadata.

`AbstractGrpcTransport::close()` delegates lifecycle cleanup to the backend. Per-call cancellation is not part of the current unary backend contract; it should be designed when a concrete backend can expose cancellable in-flight calls consistently.

`UnaryBackend::close()` is idempotent. After close, `call()` must fail predictably with `BackendClosedException`. Backend transport failures that do not produce a gRPC status may throw; `AbstractGrpcTransport` owns mapping those failures to GAX `ApiException` with `GrpcStatusCode::UNAVAILABLE`.

For the current unary slice, initial metadata and trailing/status metadata are separate in `UnaryResponse`. Backends that cannot distinguish the two may keep using `metadata`; backends that can distinguish them should put status trailers in `trailingMetadata`.

## FrankenGrpcBackend

`FrankenGrpcBackend` is the FrankenPHP bridge to grpc-go. It depends only on `UnaryBackend` inputs and outputs, not on GAX `Call` objects. Request mapping sends `UnaryRequest::path()` as the fully qualified gRPC method path, `payload` as the serialized protobuf request body, metadata as lowercase gRPC metadata, and `timeoutSeconds` as a relative duration from which the backend derives the grpc-go deadline or context timeout.

Response mapping converts grpc-go response bytes into `UnaryResponse::payload`, trailers/headers into response metadata, and grpc-go canonical status into `GrpcStatusCode` plus status message. Transport-level failures that do not produce a gRPC status should map to `GrpcStatusCode::UNAVAILABLE` unless a more precise canonical status is available.

The backend owns grpc-go channel/client lifecycle. `close()` must release backend resources, be safe to call more than once, and make later calls fail predictably. Per-call cancellation remains outside the current contract.

The PHP implementation uses an internal bridge interface so grpc-go bindings remain replaceable. The bridge accepts backend-native scalar data: path, payload, metadata, and timeout duration. `FrankenGrpcBridge` is responsible for normalizing grpc-go output into `FrankenGrpcResponse`; `FrankenGrpcBackend` maps that domain-shaped bridge response into `UnaryResponse`.

## GrpcLiteBackend

`GrpcLiteBackend` is the `php-grpc-lite` / nghttp2 backend. It must share the same `UnaryBackend` contract as `FrankenGrpcBackend`: no GAX `Call` dependency, one unary request in, one unary response or backend exception out.

The native bridge connects to the low-level `php-grpc-lite` extension surface: `Grpc\Channel`, `Grpc\Call`, and `Grpc\Timeval`. It does not route calls through the `grpc/grpc` Composer wrapper classes such as `BaseStub` or `UnaryCall`. Request mapping sends `UnaryRequest::path()` as the call method path, sends the serialized protobuf `payload` as the gRPC request message body, forwards lowercase metadata as gRPC metadata headers, and treats `timeoutSeconds` as a relative duration converted to an absolute `Grpc\Timeval` deadline.

Response mapping extracts the response message bytes into `UnaryResponse::payload`, maps initial metadata and trailing/status metadata separately, and converts the gRPC status object into `GrpcStatusCode` plus status message. Missing or malformed native status is a backend failure and is thrown so `AbstractGrpcTransport` maps it to `UNAVAILABLE`; unknown integer status values map to `GrpcStatusCode::UNKNOWN`. If nghttp2 or `php-grpc-lite` fails before a gRPC status is available, the backend may throw and let `AbstractGrpcTransport` map the failure to `UNAVAILABLE`.

`GrpcLiteBackend::close()` should release client/session resources, be idempotent, and make later calls fail with `BackendClosedException`.

`GrpcLiteTransport::build()` is the user-facing construction path. Its runtime target is `dkkoma/php-grpc-lite`, which registers a `grpc` extension, defines `Grpc\VERSION`, and provides the low-level `Grpc\*` classes consumed by `GrpcLiteNativeBridge`. The package suggests `dkkoma/php-grpc-lite` until a stable non-dev Composer constraint is available. `composer test:native-smoke` is an environment gate: it fails when the real extension surface is unavailable instead of silently passing with stubs.

## Validation Boundary

Shared value objects validate backend-facing invariants early: non-empty service/method names, valid metadata shape, positive timeouts, and canonical gRPC status values. Concrete backends may add protocol-specific validation, but they should not redefine these shared invariants.

## Public Boundary

The current backend and abstract transport types are internal implementation contracts. `UnaryBackend`, `UnaryRequest`, `UnaryResponse`, `GrpcStatusCode`, and `AbstractGrpcTransport` may change while the first concrete backends are being designed. User-facing APIs should be concrete transports or factories such as `GrpcLiteTransport::build()`.

## Current Scope

The current implementation includes Composer package setup, PHPStan level max, PHPCS, PHPUnit, `AbstractGrpcTransport`, `GrpcLiteTransport`, `UnaryBackend`, `FakeBackend`, `FrankenGrpcBackend`, `GrpcLiteBackend`, backend bridge boundaries, backend contract tests, and transport tests.
