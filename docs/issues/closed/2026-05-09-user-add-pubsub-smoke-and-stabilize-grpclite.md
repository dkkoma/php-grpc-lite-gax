# Add Pub/Sub Smoke And Stabilize GrpcLite

State: closed
Source: user instruction

## Context

The user asked to add Pub/Sub only, and to complete the remaining grpc-lite stabilization work around unary/server-streaming behavior, deadlines, cancellation, metadata, and status details.

## Proposed Fix

Add a Pub/Sub emulator smoke test using `google/cloud-pubsub` generated clients and this repository's `GrpcLiteTransport`. Review and tighten grpc-lite tests and behavior for deadline propagation, cancellation, metadata, and status/status-details handling.

## Fix Summary

Implemented and verified.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
- `docker run --rm --network php-grpc-lite_default -e PUBSUB_EMULATOR_HOST=php-grpc-lite-gax-pubsub-emulator:8085 -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify:smoke`
