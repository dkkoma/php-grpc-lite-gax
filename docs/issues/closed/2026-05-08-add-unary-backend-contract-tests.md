# Add UnaryBackend Contract Tests

State: closed
Source: work unit

## Context

`FakeBackend` has tests, but there is no reusable contract test structure for future `UnaryBackend` implementations.

## Impact

Future concrete backends may drift from the shared unary backend behavior.

## Proposed Fix

Add a backend contract test base that can be reused by backend-specific test harnesses, and apply it to `FakeBackend`.

## Fix Summary

Added `UnaryBackendContractTestCase` and applied it to `FakeBackend` with `FakeBackendContractTest`.

## Verification

- `composer lint`
- `composer test`
