# Design

## Goal

This package provides a PHP Composer library that adapts `google/gax` `TransportInterface` calls to lightweight gRPC backends. The current supported call types are unary and server streaming. The production backend paths are `php-grpc-lite` and FrankenPHP grpc-go.

## Layering

The dependency direction is:

```text
Patched GAX transportFactory
  -> Google\ApiCore\Transport\TransportInterface
  -> AbstractGrpcTransport
  -> UnaryBackend / ServerStreamingBackend
  -> concrete backend
```

`GaxTransportFactory` is the preferred user-facing entry point when the
`google/gax` transport factory patch is applied. It lets users choose
`grpc-lite`, `frankenphp-grpc-go`, or `default` without this repository carrying
Google Cloud service endpoint knowledge. GAX and google-cloud-php remain
responsible for default endpoints, emulator environment variables, universe
domain, mTLS, credentials, retry config, and generated client defaults.

`AbstractGrpcTransport` owns the GAX-facing runtime contract. It converts GAX `Call` objects and call options into backend value objects, adds GAX header credentials as request metadata, delegates to the backend, and maps backend responses back to GAX promises or `ServerStream` objects. Backend implementations must not depend on GAX client internals.

## Backend Model

`UnaryRequest` and `ServerStreamingRequest` contain the canonical service name, method name, serialized protobuf payload, request metadata, and optional timeout in seconds. Each derives the gRPC path as `/{service}/{method}`.

`UnaryResponse` contains the serialized response payload, canonical gRPC status, status message, initial metadata, and trailing/status metadata. Non-OK unary responses are mapped to `Google\ApiCore\ApiException`.

`ServerStreamingCall` exposes an iterable of serialized response payloads plus final status, initial metadata, trailing metadata, peer, and cancellation. `BackendServerStreamingCall` decodes each payload into the GAX response type and lets `Google\ApiCore\ServerStream` check the final status. Backend failures from response iteration, status reads, metadata access, peer access, and cancellation are mapped to GAX `UNAVAILABLE` failures.

`close()` is idempotent on backends. After close, backend calls must fail predictably with `BackendClosedException`.

## GrpcLiteBackend

`GrpcLiteBackend` implements both unary and server streaming contracts. The native bridge connects directly to the low-level `php-grpc-lite` extension surface: `Grpc\Channel`, `Grpc\Call`, and `Grpc\Timeval`. It does not route through `grpc/grpc` Composer wrapper classes such as `BaseStub`, `UnaryCall`, or `ServerStreamingCall`.

Unary calls send initial metadata, one request message, and close-from-client in a single batch, then receive initial metadata, one response message, and client status.

Server streaming calls send initial metadata, one request message, and close-from-client, then receive initial metadata plus messages until the native call returns no message. Initial metadata is cached so direct metadata access cannot desynchronize later response iteration. Final status is read separately with `OP_RECV_STATUS_ON_CLIENT`. Unknown integer status values map to `GrpcStatusCode::UNKNOWN`; malformed native status is a backend failure and is mapped by `AbstractGrpcTransport` to `UNAVAILABLE`.

`GrpcLiteTransport::build()` is the low-level construction path. Runtime users
provide an endpoint and optional channel options directly only when they do not
use the patched GAX `transportFactory` option. The Dev Container builds
`dkkoma/php-grpc-lite` as `grpc.so` but does not load it by default, so unit
tests use stubs. Native and emulator smoke scripts explicitly load `grpc.so`.

## FrankenPHP Extension

The FrankenPHP grpc-go path targets the `FrankenGrpc` PHP extension API from `/Users/daisuke/src/frankenphp-grpc-go-client`. `FrankenGrpcTransport::build()` is the low-level construction path. `FrankenGrpcNativeBridge` adapts `FrankenGrpc\Channel`, `UnaryCall`, `ServerStreamingCall`, `UnaryResult`, and `Status` to the repository backend contracts. Unary responses map initial metadata and trailing metadata separately. Server streaming exposes the native read loop as `ServerStreamingCall::responses()`, maps final status to `GrpcStatusCode`, and falls back to `Status::$metadata` when native trailing metadata is empty.

`docs/frankenphp-extension-api.md` remains the cross-repository contract for the byte-level extension API, including unary, server streaming, metadata, deadline, status, cancellation, and lifecycle requirements.

## Smoke Coverage

`composer test:native-smoke` verifies the real `php-grpc-lite` extension surface. `composer test:franken-smoke` clones `https://github.com/dkkoma/frankenphp-grpc-go-client` at the verified default commit, builds its FrankenPHP binary in Docker, runs PHPUnit through `frankenphp php-cli`, fails if the repository test stub is loaded, and verifies that `FrankenGrpcTransport::build()` can construct and close the bridge over the real extension. Set `FRANKEN_GRPC_CLIENT_REF=main` or another ref to test a different upstream revision. Emulator smoke suites are fail-closed and require their host environment variables.

`composer test:pubsub-smoke` runs against a Pub/Sub emulator with `google/cloud-pubsub` generated clients, using this repository's `GrpcLiteTransport`. It creates a topic and subscription, publishes a message, pulls it, and acknowledges it.

`composer test:spanner-smoke` runs against a Spanner emulator with `google/cloud-spanner` generated clients, first through `GrpcLiteTransport` in the PHP dev image and then through `FrankenGrpcTransport` in the built FrankenPHP binary. Both test cases create an instance and database, execute DML in a read-write transaction, commit, and verify `ExecuteStreamingSql` with a streaming `SELECT`.

## GAX Patch

`patches/google-gax-transport-factory.patch` targets `google/gax` 1.42.3. It
adds an optional `transportFactory` client option at the existing GAX transport
construction boundary. The callable receives the selected transport name, the
resolved API endpoint, the transport-specific config, and context such as
`clientCertSource`, `hasEmulator`, and `hasInsecureCredentials`. It must return
a `TransportInterface`.

This repository does not maintain a Google Cloud service endpoint registry.
Applications that want no transport change pass `default`, which leaves GAX's
normal transport construction untouched.

## Current Scope

The current implementation includes Composer package setup, PHPStan level max, PHPCS, PHPUnit, the GAX transport factory patch, `GaxTransportFactory`, `AbstractGrpcTransport`, `GrpcLiteTransport`, unary and server-streaming backend contracts, `FakeBackend`, `FrankenGrpcBackend`, `FrankenGrpcNativeBridge`, `GrpcLiteBackend`, contract tests, native smoke tests, Pub/Sub emulator smoke tests, grpc-lite Spanner emulator smoke tests, and FrankenPHP Spanner emulator smoke tests.
