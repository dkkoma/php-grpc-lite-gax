# Add Franken Spanner Test Case

State: closed
Source: work unit

## Context

The existing Spanner emulator smoke test is dedicated to `GrpcLiteTransport`. FrankenPHP needs its own test case so failures remain attributable to the Franken grpc-go bridge and its `frankenphp php-cli` runtime.

## Impact

Sharing one environment-switched test would mix transport assumptions and make backend-specific failures harder to diagnose.

## Proposed Fix

Add a separate Franken Spanner smoke test class that repeats the DML and streaming `SELECT` scenario using `FrankenGrpcTransport`.

## Fix Summary

Added `tests/Integration/Franken/FrankenSpannerEmulatorSmokeTest.php`, a Franken-specific Spanner emulator smoke test using `FrankenGrpcTransport`.

## Verification

- `composer verify`
- `tools/spanner-smoke.sh`
