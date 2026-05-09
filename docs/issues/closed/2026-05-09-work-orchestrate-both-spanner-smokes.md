# Orchestrate Both Spanner Smokes

State: closed
Source: work unit

## Context

The user said `composer test:spanner-smoke` may run both grpc-lite and FrankenPHP Spanner smoke cases.

## Impact

Keeping both Spanner transport checks under one command gives contributors a clear acceptance command while preserving separate test cases and runtimes.

## Proposed Fix

Add a host-side smoke script that runs the grpc-lite Spanner suite in the PHP dev image and the Franken Spanner suite through the FrankenPHP smoke runner. Wire `composer test:spanner-smoke` to that script.

## Fix Summary

Added `tools/spanner-smoke.sh`, allowed `tools/franken-smoke.sh` to accept PHPUnit arguments and Docker network/env pass-through, and wired `composer test:spanner-smoke` to the combined smoke runner.

## Verification

- `composer verify`
- `tools/spanner-smoke.sh`
- `tools/franken-smoke.sh`
- `composer test:spanner-smoke` not run directly because host Composer is not installed; the script it invokes was run successfully.
