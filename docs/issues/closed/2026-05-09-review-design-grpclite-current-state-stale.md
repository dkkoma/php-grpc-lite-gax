# Design Doc Still Marks GrpcLiteBackend As Planned

State: closed
Source: reviewer finding

## Context

Domain model re-review found that `docs/design.md` lists `GrpcLiteBackend` as planned in the `Layering` section, while the repository now includes `GrpcLiteBackend`, `GrpcLiteNativeBridge`, `GrpcLiteTransport::build()`, tests, and closed implementation issues.

## Impact

The design document is supposed to describe the latest current design only. Marking an implemented backend as planned weakens contributor guidance around what is stable enough to review and where future work should focus.

## Proposed Fix

Update the `Layering` section so `GrpcLiteBackend` is listed as current, matching the later `GrpcLiteBackend` section and `Current Scope`.

## Fix Summary

Updated the `Layering` section in `docs/design.md` so `GrpcLiteBackend` is listed as current implementation.

## Verification

Verified by reviewing `docs/design.md` and running `composer lint`.
