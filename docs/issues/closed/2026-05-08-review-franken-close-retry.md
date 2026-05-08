# Review Finding: Retry Franken Bridge Cleanup After Close Failure

State: closed
Source: domain model reviewer

## Context

`FrankenGrpcBackend::close()` marks the backend closed before releasing bridge resources.

## Impact

If bridge cleanup throws, later `close()` calls can return early and skip retrying resource cleanup.

## Proposed Fix

Set the closed state only after bridge cleanup succeeds.

## Fix Summary

Changed `FrankenGrpcBackend::close()` to set closed state only after bridge cleanup succeeds.

## Verification

- `composer lint`
- `composer test`
