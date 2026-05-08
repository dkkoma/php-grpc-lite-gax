# Review Finding: Model gRPC Status Explicitly

State: closed
Source: domain model reviewer

## Context

`UnaryResponse` originally exposed an unconstrained integer `statusCode`.

## Impact

Invalid or ambiguous status values could reach GAX error mapping.

## Proposed Fix

Replace the integer with a canonical gRPC status model.

## Fix Summary

Added `GrpcStatusCode` enum and changed `UnaryResponse` to use `grpcStatusCode`.

## Verification

- `composer lint`
- `composer test`
