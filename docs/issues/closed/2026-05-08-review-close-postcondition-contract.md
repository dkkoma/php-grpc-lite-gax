# Review Finding: Contract-Test Close Postcondition

State: closed
Source: domain model reviewer

## Context

The backend lifecycle design says calls after close should fail predictably, but the contract test only checks that `close()` itself does not throw.

## Impact

Backends could keep accepting calls after close while passing contract tests.

## Proposed Fix

Add a reusable contract assertion for call-after-close behavior.

## Fix Summary

Added a reusable contract test asserting calls after close fail with a `RuntimeException`.

## Verification

- `composer test`
