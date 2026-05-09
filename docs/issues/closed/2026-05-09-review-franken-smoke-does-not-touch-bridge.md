# Review: Franken Smoke Does Not Touch Bridge

State: closed
Source: domain-model and test reviewers

## Finding

The `franken-smoke` suite verifies real `FrankenGrpc` class loading but does not instantiate this repository's bridge or transport boundary.

## Proposed Fix

Extend the smoke test to build and close `FrankenGrpcTransport` against the real extension. Keep real unary/server-streaming RPC smoke coverage pending until a Franken test endpoint is available in this repository's test image.

## Fix Summary

Extended `franken-smoke` to build and close `FrankenGrpcTransport` over the real extension surface while still failing if the repository stub is loaded.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
