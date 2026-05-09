# Transport Factory Bypasses GAX Default Port Normalization

State: closed
Source: reviewer finding

## Context

Transport behavior review found that the patched `GapicClientTrait::createTransport()` calls `transportFactory` with the raw resolved `apiEndpoint`, before the normal GAX transport build path calls `ServiceAddressTrait::normalizeServiceAddress()`. GAX accepts endpoints without an explicit port and its built-in gRPC transport normalizes them to `host:443`; `GaxTransportFactory::grpcLite()` and `GaxTransportFactory::franken()` pass the raw endpoint directly to their native channels.

## Impact

Applications that supply a valid GAX `apiEndpoint` such as `spanner.googleapis.com` get different behavior after enabling `transportFactory`: the custom backend receives `spanner.googleapis.com` instead of the `spanner.googleapis.com:443` target used by GAX's existing `createTransport` flow. Depending on native channel behavior, this can fail connection setup or silently use a backend-specific default instead of GAX's documented default port.

## Proposed Fix

Preserve GAX endpoint semantics in the factory path. Normalize the endpoint before invoking `transportFactory`, or have this package normalize host-only endpoints to `:443` before building `GrpcLiteTransport` and `FrankenGrpcTransport`. Add coverage for explicit-port and omitted-port endpoints.

## Fix Summary

Added endpoint normalization in `GaxTransportFactory` so host-only endpoints get `:443` before constructing grpc-lite or Franken transports, while explicit host:port endpoints pass through unchanged.

## Verification

Verified with `composer test`, `composer lint`, `composer verify`, and GAX patch forward/reverse dry-runs in the PHP 8.4 dev container.
