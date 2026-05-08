# Review Finding: Reject Non-Finite Timeouts At Domain Boundary

State: closed
Source: domain model reviewer

## Context

`timeoutMillis` and `UnaryRequest::timeoutSeconds` accept non-finite floats such as `INF` or `NAN`.

## Impact

Concrete backends could receive invalid timeout durations.

## Proposed Fix

Reject non-finite timeout values at both the GAX transport boundary and backend value object boundary.

## Fix Summary

Added finite positive timeout validation in `AbstractGrpcTransport` and `UnaryRequest`.

## Verification

- `composer lint`
- `composer test`
