# Review Finding: Preserve Non-OK FakeBackend Responses

State: closed
Source: test reviewer

## Context

FakeBackend tests only covered default OK responses.

## Impact

Higher-level tests might not be able to rely on FakeBackend for error/status behavior.

## Proposed Fix

Add a non-OK response preservation test.

## Fix Summary

Added coverage for a queued `GrpcStatusCode::UNAVAILABLE` response and transport-level `ApiException` mapping.

## Verification

- `composer test`
