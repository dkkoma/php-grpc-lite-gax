# Design Doc Still Marks GrpcLiteBackend As Planned

State: open
Source: reviewer finding

## Context

Domain model review finding. `docs/design.md` says `GrpcLiteBackend` is "Planned" in the implementation list, while the current change adds `src/Backend/GrpcLite/GrpcLiteBackend.php`, `GrpcLiteNativeBridge`, `GrpcLiteTransport`, and tests. The same document later lists these as part of the current implementation.

## Impact

The design document is supposed to represent the latest current design only. Mixed planned/current wording makes the repository model ambiguous for contributors deciding which backend contracts are implemented and reviewable.

## Proposed Fix

Update `docs/design.md` so `GrpcLiteBackend` and `GrpcLiteTransport` are consistently described as current implementation, while any not-yet-built `php-grpc-lite` work is named more specifically.

## Fix Summary

Fill this in when closing the issue. Summarize what changed and where.

## Verification

not run; reviewer finding only.
