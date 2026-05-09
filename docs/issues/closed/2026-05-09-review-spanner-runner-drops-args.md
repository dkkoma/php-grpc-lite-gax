# Spanner Runner Drops PHPUnit Args

State: closed
Source: reviewer finding

## Context

The test/CI reviewer found that `tools/spanner-smoke.sh` does not forward script arguments to either PHPUnit invocation.

## Impact

Local debugging with PHPUnit flags such as `--filter`, `--debug`, or `--stop-on-failure` is inconsistent with `tools/franken-smoke.sh`.

## Proposed Fix

Forward extra script arguments to both the grpc-lite and FrankenPHP Spanner PHPUnit invocations while keeping each suite explicit.

## Fix Summary

`tools/spanner-smoke.sh` now forwards extra arguments to both the grpc-lite and FrankenPHP PHPUnit invocations while keeping each testsuite explicit.

## Verification

- `composer verify`
- `SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 tools/spanner-smoke.sh`
