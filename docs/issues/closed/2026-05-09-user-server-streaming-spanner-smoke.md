# Add Server Streaming And Spanner Smoke

State: closed
Source: user instruction

## Context

The user asked to open the server streaming path and verify it with Spanner emulator smoke tests covering Spanner DML and SELECT.

## Impact

Unary-only transport is not enough for Spanner-style workloads. Server streaming establishes the next GAX call type and validates that `php-grpc-lite` can support real google-cloud-php style streaming queries.

## Proposed Fix

Add a server streaming backend contract, wire `AbstractGrpcTransport::startServerStreamingCall()`, implement the `GrpcLiteBackend` path over low-level `Grpc\Call`, and add emulator-backed smoke tests/scripts for Spanner DML and streaming SELECT.

## Fix Summary

Implemented server streaming through `AbstractGrpcTransport`, backend contracts, `GrpcLiteBackend`, and the low-level `Grpc\Call` receive path. Added Spanner emulator smoke coverage for DML and streaming `SELECT` through `google/cloud-spanner` generated clients using `GrpcLiteTransport`.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
