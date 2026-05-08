# Review Finding: Remove Or Document Unused Fixture Options

State: closed
Source: domain model reviewer

## Context

The GAX-like fixture includes options the transport does not consume.

## Impact

Tests can imply behavior that is not part of the current transport contract.

## Proposed Fix

Remove unused options from the fixture or document that they are intentionally ignored.

## Fix Summary

Removed unused `retryAttempt` and `serviceName` options from the GAX-like fixture.

## Verification

- `composer lint`
- `composer test`
