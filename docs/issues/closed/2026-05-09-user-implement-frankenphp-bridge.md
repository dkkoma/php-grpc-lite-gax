# Implement FrankenPHP Bridge

State: closed
Source: user instruction

## Context

The user created the FrankenPHP gRPC Go client extension in `/Users/daisuke/src/frankenphp-grpc-go-client` and asked to implement this repository's FrankenPHP bridge, including server streaming and tests.

## Proposed Fix

Add a native `FrankenGrpc` extension bridge for unary and server streaming, update `FrankenGrpcBackend` to implement `ServerStreamingBackend`, add extension stubs for unit tests, and cover request/response/status/metadata/lifecycle behavior.

## Fix Summary

Implemented the FrankenPHP native bridge for unary and server streaming, added test stubs and unit coverage, and updated the design document.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
