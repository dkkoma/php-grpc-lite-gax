# Review Finding: Assert Franken Bridge Closes Once

State: closed
Source: test reviewer

## Context

`FrankenGrpcBackendTest` calls `close()` twice, but the fake bridge only records a boolean closed state.

## Impact

The test would not detect duplicate bridge close calls.

## Proposed Fix

Track bridge close count in the fake and assert exactly one close call for idempotent backend close.

## Fix Summary

Added a close-call counter to `FakeFrankenGrpcBridge` and asserted the bridge closes exactly once when backend close is called twice.

## Verification

- `composer test`
