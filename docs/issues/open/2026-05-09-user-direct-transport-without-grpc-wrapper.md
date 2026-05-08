# Use TransportInterface Without grpc/grpc Runtime Wrapper

State: open
Source: user instruction

## Context

The user clarified that this package should let google-cloud-php users provide a `TransportInterface` implementation without routing calls through the `grpc/grpc` `BaseStub` / `UnaryCall` wrapper path.

## Impact

The final package shape must keep the GAX-facing transport separate from the official wrapper runtime path. Otherwise the adapter would not provide the intended shortcut through `php-grpc-lite`.

## Proposed Fix

Implement the `GrpcLiteBackend` integration against the low-level `php-grpc-lite` extension surface (`Grpc\Channel`, `Grpc\Call`, `Grpc\Timeval`) and provide a concrete transport entry point for users.

## Fix Summary

Fill this in when closing the issue.

## Verification

List verification commands, review steps, or acceptance notes. Use `not run` with a reason when verification is skipped.
