# Spanner Runner Default Host

State: closed
Source: reviewer finding

## Context

The test/CI reviewer found that `tools/spanner-smoke.sh` supplies a default `SPANNER_EMULATOR_HOST`.

## Impact

Defaulting the host bypasses the smoke suite's fail-closed missing-env behavior and can turn a configuration problem into a less clear connection failure.

## Proposed Fix

Require `SPANNER_EMULATOR_HOST` explicitly in the wrapper and fail before running Docker when it is missing.

## Fix Summary

`tools/spanner-smoke.sh` now requires `SPANNER_EMULATOR_HOST` and exits before Docker execution when it is missing.

## Verification

- `env -u SPANNER_EMULATOR_HOST tools/spanner-smoke.sh`
- `SPANNER_EMULATOR_HOST=php-grpc-lite-spanner-emulator-1:9010 tools/spanner-smoke.sh`
