# Stabilize GrpcLite Semantics

State: closed
Source: work unit

## Context

Unary and server streaming are implemented, but their operational semantics need focused checks before moving toward wider google-cloud-php usage.

## Proposed Fix

Audit and strengthen tests for deadline conversion, cancellation, metadata/trailing metadata behavior, and status/status-details mapping in `GrpcLiteBackend`, `GrpcLiteNativeBridge`, `GrpcLiteNativeServerStreamingCall`, and `AbstractGrpcTransport`.

## Fix Summary

Implemented and verified.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e PUBSUB_EMULATOR_HOST=php-grpc-lite-gax-pubsub-emulator:8085 -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify:smoke`
