# Review Finding: Contract Test Must Not Model Fake Queue

State: closed
Source: domain model reviewer

## Context

`UnaryBackendContractTestCase` accepts a list of queued responses, which models `FakeBackend` mechanics rather than the backend contract.

## Impact

Real backends could need fake-only queue mechanics to reuse the contract test.

## Proposed Fix

Reshape contract tests around behavior-specific backend harness methods instead of response queues.

## Fix Summary

Replaced queue-shaped contract setup with behavior-specific harness methods: `createBackendForOkUnary()`, `createBackendForStatus()`, and `createBackendForLifecycle()`.

## Verification

- `composer lint`
- `composer test`
