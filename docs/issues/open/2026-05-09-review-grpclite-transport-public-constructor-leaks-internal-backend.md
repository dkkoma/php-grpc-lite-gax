# GrpcLiteTransport Public Constructor Leaks Internal Backend Contract

State: open
Source: reviewer finding

## Context

Domain model review finding. `src/Transport/GrpcLiteTransport.php` is the user-facing `TransportInterface` entry point, but its public constructor accepts `UnaryBackend`. `UnaryBackend`, `GrpcLiteBackend`, and the bridge layer are documented as internal implementation contracts in `docs/design.md`.

## Impact

The public transport boundary exposes the internal backend model to google-cloud-php users. That makes the unstable `UnaryBackend` contract look like supported API and weakens the intended lifecycle where users construct a concrete transport through a stable endpoint/options factory.

## Proposed Fix

Keep `GrpcLiteTransport::build()` as the public construction path and hide backend injection behind an internal or test-only factory. If direct injection remains necessary, document it explicitly as unstable and avoid presenting it as the normal user entry point.

## Fix Summary

Fill this in when closing the issue. Summarize what changed and where.

## Verification

not run; reviewer finding only.
