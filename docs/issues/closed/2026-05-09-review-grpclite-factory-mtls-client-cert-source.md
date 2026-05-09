# GrpcLite Factory Drops GAX mTLS Client Certificate Source

State: closed
Source: reviewer finding

## Context

Transport behavior review found that GAX's built-in gRPC path turns `clientCertSource` into SSL channel credentials when `stubOpts['credentials']` is not already set. The patch passes `clientCertSource` in the transport config and context, but `GaxTransportFactory::grpcLite()` extracts only `stubOpts` and `GrpcLiteNativeBridge` falls back to plain `ChannelCredentials::createSsl()` when credentials are absent.

## Impact

Clients using GAX mTLS environment handling can resolve the mTLS endpoint but lose the client certificate when switching to `GaxTransportFactory::forBackend('grpc-lite')`. That diverges from GAX's existing `createTransport` behavior and contradicts the design goal that GAX remains responsible for mTLS and generated client defaults.

## Proposed Fix

Propagate `clientCertSource` through the grpc-lite factory path with the same precedence as GAX: keep explicit `stubOpts['credentials']` when present, otherwise call the client cert source and create SSL channel credentials with the returned cert/key. Add a focused test that uses a fake client cert source and asserts grpc-lite channel credentials are built from it.

## Fix Summary

Added grpc-lite factory handling for `clientCertSource`: explicit `stubOpts['credentials']` still wins, otherwise the factory builds SSL channel credentials from the certificate source. Updated test stubs and added coverage for mTLS and explicit credential precedence.

## Verification

Verified with `composer test`, `composer lint`, `composer verify`, and GAX patch forward/reverse dry-runs in the PHP 8.4 dev container.
