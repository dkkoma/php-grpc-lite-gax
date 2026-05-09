# Implement Server Streaming Transport Path

## State

closed

## Source

work unit

## Description

Add the server streaming path from GAX `TransportInterface` through `AbstractGrpcTransport` into backend contracts and `php-grpc-lite`.

## Proposed Fix

Introduce `ServerStreamingBackend`, `ServerStreamingRequest`, and `ServerStreamingCall`. Adapt `AbstractGrpcTransport::startServerStreamingCall()` to return a GAX `ServerStream`, and implement the low-level `Grpc\Call` receive loop in `GrpcLiteNativeServerStreamingCall`.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
