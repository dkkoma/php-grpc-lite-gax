# GaxTransportFactory Should Reject Non-gRPC GAX Transports

State: closed
Source: reviewer finding

## Context

Domain model review for the GAX `transportFactory` patch and
`GaxTransportFactory` changes found that the repository factory builds
repository gRPC backends without checking the selected GAX transport name.

The design describes `GaxTransportFactory` as the preferred user-facing entry
point for choosing repository backend values `grpc-lite`,
`frankenphp-grpc-go`, or `default`, while GAX remains responsible for endpoint
and transport config resolution. The README examples set `'transport' =>
'grpc'`, and both non-default repository backends are gRPC transports.

Evidence:

- `src/GaxTransportFactory.php`: `grpcLite()` receives `$transport` and
  `$context` but unsets both before constructing `GrpcLiteTransport`.
- `src/GaxTransportFactory.php`: `franken()` receives `$transport` and
  `$transportConfig` but unsets both before constructing
  `FrankenGrpcTransport`.
- `patches/google-gax-transport-factory.patch`: the patch invokes
  `transportFactory` for `grpc`, `grpc-fallback`, and `rest`, so the repository
  factory can currently be paired with a non-gRPC GAX transport selection.

Severity: Medium

## Impact

This collapses two different domain concepts:

- GAX transport selection: `grpc`, `grpc-fallback`, or `rest`.
- Repository backend selection: `grpc-lite`, `frankenphp-grpc-go`, or
  `default`.

If an application accidentally configures a generated client with
`'transport' => 'rest'` or `'grpc-fallback'` and this repository's
`GaxTransportFactory::forBackend('grpc-lite')` or
`GaxTransportFactory::forBackend('frankenphp-grpc-go')`, the factory will still
return a gRPC `TransportInterface` built from a config shape that belongs to a
different GAX transport. That makes the construction boundary fail late or
silently ignore transport-specific invariants instead of rejecting an invalid
domain combination.

## Proposed Fix

Have the repository-provided factories validate that the selected GAX transport
name is `grpc` before constructing `GrpcLiteTransport` or
`FrankenGrpcTransport`. Throw a clear `InvalidArgumentException` or GAX-facing
validation exception message when the patched factory is invoked for `rest` or
`grpc-fallback`.

Add focused tests for both `grpcLite()` and `franken()` that call the returned
factory with a non-`grpc` transport name and assert the clear failure.

## Fix Summary

Added GAX transport-name validation in `GaxTransportFactory` so repository backends only accept patched GAX transport `grpc`. Added focused tests for both grpc-lite and Franken factories rejecting non-gRPC transport selections.

## Verification

Verified with `composer test`, `composer lint`, `composer verify`, and GAX patch forward/reverse dry-runs in the PHP 8.4 dev container.
