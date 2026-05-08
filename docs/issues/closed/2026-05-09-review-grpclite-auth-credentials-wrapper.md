# GrpcLite transport ignores GAX credentials wrapper

State: closed
Source: reviewer finding

## Context

Transport behavior review found that `AbstractGrpcTransport::buildUnaryRequest()` only reads `headers` and `timeoutMillis`. GAX passes `credentialsWrapper` and `audience` through `CredentialsWrapperMiddleware`, and the stock `GrpcTransport` converts that wrapper into per-call credentials. The current `GrpcLiteNativeBridge` creates a low-level `Grpc\Call` but never attaches authorization metadata or call credentials.

## Impact

google-cloud-php users can pass this repository's `TransportInterface` implementation and avoid the `grpc/grpc` wrapper runtime path, but authenticated Google API calls may be sent without bearer/API-key authorization unless the caller manually injects headers. That breaks the primary public use case.

## Proposed Fix

Decide the low-level `php-grpc-lite` auth mapping and implement it without routing through `grpc/grpc` wrapper classes. Either normalize `credentialsWrapper->getAuthorizationHeaderCallback($audience)` into request metadata before creating `UnaryRequest`, or expose a bridge call option that maps to `Grpc\Call::setCredentials()` with low-level call credentials if available. Add transport tests for bearer/API-key headers and no-auth credentials.

## Fix Summary

`AbstractGrpcTransport` now accepts `HeaderCredentialsInterface`, checks universe domain, resolves authorization headers, preserves user-provided authorization headers, validates audience type, and includes tests for bearer/API-key style metadata and no-auth callbacks.

## Verification

Fixed in `20763ad`. Verified with `composer lint` and `composer test:coverage`.
