# Add GrpcLiteTransport Entry Point

State: open
Source: work unit

## Context

Users need a concrete `TransportInterface` object they can pass to google-cloud-php clients through the `transport` option.

## Impact

Without a transport entry point, users would have to assemble internal backend objects directly and the final package workflow would remain unclear.

## Proposed Fix

Add `GrpcLiteTransport` as a small concrete `TransportInterface` implementation extending the shared unary transport and provide a `build()` factory for endpoint/channel options.

## Fix Summary

Fill this in when closing the issue.

## Verification

List verification commands, review steps, or acceptance notes. Use `not run` with a reason when verification is skipped.
