# Review Finding: Define Transport Lifecycle Boundary

State: closed
Source: domain model reviewer

## Context

`TransportInterface::close()` is empty, `UnaryBackend` has no lifecycle contract, and promise cancellation has no backend callback.

## Impact

Future concrete backends may hold channels, handles, or in-flight calls that cannot be closed through the transport abstraction.

## Proposed Fix

Define a minimal backend lifecycle boundary for close. Document that per-call cancellation is not yet supported for this unary slice.

## Fix Summary

Added `UnaryBackend::close()` and made `AbstractGrpcTransport::close()` delegate to the backend. Documented that per-call cancellation is not yet part of the backend contract.

## Verification

- `composer lint`
- `composer test`
