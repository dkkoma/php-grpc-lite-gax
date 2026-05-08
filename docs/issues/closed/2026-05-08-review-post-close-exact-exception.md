# Review Finding: Assert Exact Post-Close Exception

State: closed
Source: domain model reviewer

## Context

`UnaryBackendContractTestCase` expects only `RuntimeException` after close, while the design says post-close calls must fail predictably.

## Impact

A backend could pass the contract by throwing an unrelated runtime failure instead of modeling closed state.

## Proposed Fix

Require `BackendClosedException` for post-close calls in the reusable contract.

## Fix Summary

Changed the reusable backend contract to require `BackendClosedException` after close.

## Verification

- `composer lint`
- `composer test`
