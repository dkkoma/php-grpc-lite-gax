# Add Franken Spanner Smoke

State: closed
Source: user instruction

## Context

The user pointed out that the existing Spanner emulator smoke already covers DML and streaming `SELECT`, so FrankenPHP smoke should exercise the same path instead of stopping at extension surface construction.

## Impact

Without a FrankenPHP Spanner smoke path, the grpc-go bridge is not validated against the existing google-cloud-php Spanner workload that proves unary and server streaming behavior.

## Proposed Fix

Make the Spanner smoke test select `GrpcLiteTransport` or `FrankenGrpcTransport`, extend the Franken smoke runner to pass through emulator environment and PHPUnit arguments, and add a Composer script for Franken Spanner smoke.

## Fix Summary

Added a separate FrankenPHP Spanner emulator smoke test for DML and streaming `SELECT`, wired `composer test:spanner-smoke` to run both grpc-lite and FrankenPHP Spanner paths, and updated the design document.

## Verification

- `composer verify`
- `tools/spanner-smoke.sh`
- `tools/franken-smoke.sh`
- `composer test:spanner-smoke` not run directly because host Composer is not installed; the script it invokes was run successfully.
