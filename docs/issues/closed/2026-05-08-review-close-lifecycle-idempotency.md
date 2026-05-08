# Review Finding: Define Close Lifecycle Semantics

State: closed
Source: test reviewer

## Context

`UnaryBackend` contract tests do not define post-close behavior or idempotency.

## Impact

Implementations can diverge on lifecycle semantics.

## Proposed Fix

Document close idempotency and require calls after close to fail predictably.

## Fix Summary

Documented idempotent close semantics and added contract coverage for double close plus call-after-close failure.

## Verification

- `composer test`
