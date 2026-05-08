# Add GrpcLiteTransport Entry Point

State: closed
Source: work unit

## Context

Users need a concrete `TransportInterface` object they can pass to google-cloud-php clients through the `transport` option.

## Impact

Without a transport entry point, users would have to assemble internal backend objects directly and the final package workflow would remain unclear.

## Proposed Fix

Add `GrpcLiteTransport` as a small concrete `TransportInterface` implementation extending the shared unary transport and provide a `build()` factory for endpoint/channel options.

## Fix Summary

Added `GrpcLiteTransport::build()` as the user-facing `TransportInterface` construction path for google-cloud-php clients.

## Verification

Fixed in `20763ad`. Verified with `composer lint` and `composer test:coverage`.
