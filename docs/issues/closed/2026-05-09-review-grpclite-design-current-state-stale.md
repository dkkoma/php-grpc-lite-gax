# Design Doc Still Marks GrpcLiteBackend As Planned

State: closed
Source: reviewer finding

## Context

Domain model review finding. `docs/design.md` says `GrpcLiteBackend` is "Planned" in the implementation list, while the current change adds `src/Backend/GrpcLite/GrpcLiteBackend.php`, `GrpcLiteNativeBridge`, `GrpcLiteTransport`, and tests. The same document later lists these as part of the current implementation.

## Impact

The design document is supposed to represent the latest current design only. Mixed planned/current wording makes the repository model ambiguous for contributors deciding which backend contracts are implemented and reviewable.

## Proposed Fix

Update `docs/design.md` so `GrpcLiteBackend` and `GrpcLiteTransport` are consistently described as current implementation, while any not-yet-built `php-grpc-lite` work is named more specifically.

## Fix Summary

Updated `docs/design.md` so `GrpcLiteBackend`, `GrpcLiteNativeBridge`, and `GrpcLiteTransport` are described as current implementation, with current metadata, auth, runtime, and public boundary behavior.

## Verification

Fixed in `20763ad`. Verified by reviewing `docs/design.md` and running `composer lint`.
