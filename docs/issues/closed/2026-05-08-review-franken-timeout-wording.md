# Review Finding: Clarify Franken Timeout Duration

State: closed
Source: domain model reviewer

## Context

`docs/design.md` describes `timeoutSeconds` as a timeout duration but later says FrankenGrpcBackend sends it as a deadline.

## Impact

Future implementation could confuse relative timeout duration with absolute deadline.

## Proposed Fix

Clarify that `timeoutSeconds` is a relative duration from which FrankenGrpcBackend derives a grpc-go deadline or context timeout.

## Fix Summary

Clarified that `timeoutSeconds` is a relative duration used to derive grpc-go deadline or context timeout.

## Verification

Reviewed `docs/design.md`.
