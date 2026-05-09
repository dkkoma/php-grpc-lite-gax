# Add Spanner Emulator Smoke Test

## State

closed

## Source

work unit

## Description

Verify the implemented transport against real Google Cloud Spanner generated clients without relying on `grpc/grpc` call wrappers.

## Proposed Fix

Add a `spanner-smoke` PHPUnit suite that uses `GrpcLiteTransport` with `google/cloud-spanner` clients against the Spanner emulator. Cover DML execution and server-streaming `SELECT`.

## Verification

- `docker run --rm --network php-grpc-lite_default -e SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer test:spanner-smoke`
