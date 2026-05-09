# Add Franken Native Bridge

State: closed
Source: work unit

## Context

The existing `FrankenGrpcBackend` uses an internal bridge interface but only supports unary calls and has no concrete bridge to the new `FrankenGrpc` PHP extension API.

## Proposed Fix

Implement `FrankenGrpcNativeBridge` and `FrankenGrpcNativeServerStreamingCall`, extend the bridge interface for server streaming, and map `FrankenGrpc\Status` / metadata / trailers into the repository backend model.

## Fix Summary

Implemented the FrankenPHP native bridge for unary and server streaming, added test stubs and unit coverage, and updated the design document.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
