# Optimal GAX Transport Factory Patch

State: open
Source: user instruction

## Context

The user wants to evaluate whether patching `google/gax` with Composer patches
is the best route for this library, and if so whether the proposed
`transportFactory` approach is the optimal patch shape.

## Impact

A local GAX patch can solve endpoint resolution without replacing Google Cloud
client bindings, but a poorly shaped patch can diverge from upstream GAX and
become hard to maintain.

## Proposed Fix

Design the patch around GAX's existing transport construction boundary: keep
endpoint, emulator, universe domain, mTLS, credentials, and client defaults in
GAX/google-cloud-php, and allow a user-supplied factory only at the point where
GAX would otherwise call `GrpcTransport::build()`, `RestTransport::build()`, or
`GrpcFallbackTransport::build()`.

## Fix Summary


## Verification

Not run; issue capture only.
