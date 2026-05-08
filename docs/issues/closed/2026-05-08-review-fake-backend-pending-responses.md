# Review Finding: Assert Pending FakeBackend Responses

State: closed
Source: domain model reviewer

## Context

`FakeBackend` could enqueue extra responses without tests noticing.

## Impact

Tests could pass while exercising fewer backend calls than expected.

## Proposed Fix

Expose a test helper to assert that all queued responses were consumed.

## Fix Summary

Added `pendingResponseCount()` and `assertNoPendingResponses()` to `FakeBackend` with tests.

## Verification

- `composer lint`
- `composer test`
