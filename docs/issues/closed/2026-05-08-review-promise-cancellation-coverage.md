# Review Finding: Cover Promise Cancellation Behavior

State: closed
Source: test reviewer

## Context

Testing guidance mentions cancellation, but the promise returned by `AbstractGrpcTransport::startUnaryCall()` has no cancellation coverage.

## Impact

Cancel-before-wait behavior remains undocumented and unguarded.

## Proposed Fix

Add a test that cancels the promise before waiting and verifies backend execution does not occur.

## Fix Summary

Added cancellation coverage showing cancel-before-wait does not call the backend and raises `CancellationException` on wait.

## Verification

- `composer test`
