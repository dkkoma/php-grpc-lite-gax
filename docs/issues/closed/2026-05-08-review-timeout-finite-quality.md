# Review Finding: Add Non-Finite Timeout Regression Tests

State: closed
Source: test reviewer

## Context

`timeoutMillis` accepts non-finite floats such as `INF`, `-INF`, and `NAN`.

## Impact

Backend-specific timeout failures can replace deterministic validation.

## Proposed Fix

Reject non-finite values and add regression coverage.

## Fix Summary

Added regression coverage for `INF`, `-INF`, and `NAN` timeout values.

## Verification

- `composer test`
