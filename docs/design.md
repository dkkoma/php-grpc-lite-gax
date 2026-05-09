# Design

## Goal

This package provides a PHP Composer library that adapts `google/gax` `TransportInterface` calls to lightweight gRPC backends. The current supported call types are unary and server streaming. The production backend paths are `php-grpc-lite` and FrankenPHP grpc-go.

## Layering

The dependency direction is:

```text
Google\ApiCore\Transport\TransportInterface
  -> AbstractGrpcTransport
  -> UnaryBackend / ServerStreamingBackend
  -> concrete backend
```

`AbstractGrpcTransport` owns the GAX-facing contract. It converts GAX `Call` objects and call options into backend value objects, adds GAX header credentials as request metadata, delegates to the backend, and maps backend responses back to GAX promises or `ServerStream` objects. Backend implementations must not depend on GAX client internals.

## Backend Model

`UnaryRequest` and `ServerStreamingRequest` contain the canonical service name, method name, serialized protobuf payload, request metadata, and optional timeout in seconds. Each derives the gRPC path as `/{service}/{method}`.

`UnaryResponse` contains the serialized response payload, canonical gRPC status, status message, initial metadata, and trailing/status metadata. Non-OK unary responses are mapped to `Google\ApiCore\ApiException`.

`ServerStreamingCall` exposes an iterable of serialized response payloads plus final status, initial metadata, trailing metadata, peer, and cancellation. `BackendServerStreamingCall` decodes each payload into the GAX response type and lets `Google\ApiCore\ServerStream` check the final status. Backend failures from response iteration, status reads, metadata access, peer access, and cancellation are mapped to GAX `UNAVAILABLE` failures.

`close()` is idempotent on backends. After close, backend calls must fail predictably with `BackendClosedException`.

## GrpcLiteBackend

`GrpcLiteBackend` implements both unary and server streaming contracts. The native bridge connects directly to the low-level `php-grpc-lite` extension surface: `Grpc\Channel`, `Grpc\Call`, and `Grpc\Timeval`. It does not route through `grpc/grpc` Composer wrapper classes such as `BaseStub`, `UnaryCall`, or `ServerStreamingCall`.

Unary calls send initial metadata, one request message, and close-from-client in a single batch, then receive initial metadata, one response message, and client status.

Server streaming calls send initial metadata, one request message, and close-from-client, then receive initial metadata plus messages until the native call returns no message. Initial metadata is cached so direct metadata access cannot desynchronize later response iteration. Final status is read separately with `OP_RECV_STATUS_ON_CLIENT`. Unknown integer status values map to `GrpcStatusCode::UNKNOWN`; malformed native status is a backend failure and is mapped by `AbstractGrpcTransport` to `UNAVAILABLE`.

`GrpcLiteTransport::build()` is the user-facing construction path. Runtime users provide an endpoint and optional channel options. The Dev Container builds `dkkoma/php-grpc-lite` as `grpc.so` but does not load it by default, so unit tests use stubs. Native and emulator smoke scripts explicitly load `grpc.so`.

## FrankenPHP Extension

The FrankenPHP grpc-go path targets the `FrankenGrpc` PHP extension API from `/Users/daisuke/src/frankenphp-grpc-go-client`. `FrankenGrpcTransport::build()` is the user-facing construction path. `FrankenGrpcNativeBridge` adapts `FrankenGrpc\Channel`, `UnaryCall`, `ServerStreamingCall`, `UnaryResult`, and `Status` to the repository backend contracts. Unary responses map initial metadata and trailing metadata separately. Server streaming exposes the native read loop as `ServerStreamingCall::responses()`, maps final status to `GrpcStatusCode`, and falls back to `Status::$metadata` when native trailing metadata is empty.

`docs/frankenphp-extension-api.md` remains the cross-repository contract for the byte-level extension API, including unary, server streaming, metadata, deadline, status, cancellation, and lifecycle requirements.

## Smoke Coverage

`composer test:native-smoke` verifies the real `php-grpc-lite` extension surface. `composer test:franken-smoke` clones `https://github.com/dkkoma/frankenphp-grpc-go-client`, builds its FrankenPHP binary in Docker, runs PHPUnit through `frankenphp php-cli`, fails if the repository test stub is loaded, and verifies that `FrankenGrpcTransport::build()` can construct and close the bridge over the real extension. Unary and server-streaming RPC smoke coverage for FrankenPHP remains pending until a Franken test endpoint is available. Emulator smoke suites are fail-closed and require their host environment variables.

`composer test:pubsub-smoke` runs against a Pub/Sub emulator with `google/cloud-pubsub` generated clients, using this repository's `GrpcLiteTransport`. It creates a topic and subscription, publishes a message, pulls it, and acknowledges it.

`composer test:spanner-smoke` runs against a Spanner emulator with `google/cloud-spanner` generated clients, using this repository's `GrpcLiteTransport`. It creates an instance and database, executes DML in a read-write transaction, commits, and verifies `ExecuteStreamingSql` with a streaming `SELECT`.

## Current Scope

The current implementation includes Composer package setup, PHPStan level max, PHPCS, PHPUnit, `AbstractGrpcTransport`, `GrpcLiteTransport`, unary and server-streaming backend contracts, `FakeBackend`, `FrankenGrpcBackend`, `FrankenGrpcNativeBridge`, `GrpcLiteBackend`, contract tests, native smoke tests, Pub/Sub emulator smoke tests, and Spanner emulator smoke tests.
