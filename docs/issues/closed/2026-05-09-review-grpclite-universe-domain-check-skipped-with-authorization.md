# GrpcLite Transport Skips Universe Domain Check When Authorization Header Exists

State: closed
Source: reviewer finding

## Context

Transport behavior re-review found that `AbstractGrpcTransport::headersWithCredentials()` only calls `HeaderCredentialsInterface::checkUniverseDomain()` when it is also going to resolve authorization headers from `credentialsWrapper`. If callers provide `Authorization` or `authorization` in `headers`, the credentials wrapper is ignored completely. Stock GAX `GrpcTransport::startUnaryCall()` checks universe domain whenever `credentialsWrapper` is present, independent of whether request headers already contain authorization.

## Impact

google-cloud-php callers normally pass `credentialsWrapper` through GAX middleware. Skipping the universe-domain check when an explicit auth header exists diverges from GAX gRPC transport behavior and can bypass credential validation that should still run for the configured client credentials.

## Proposed Fix

Validate `credentialsWrapper` type and call `checkUniverseDomain()` whenever the option is present. Keep the existing behavior that user-supplied authorization metadata is not overwritten, but separate universe-domain validation from authorization header generation. Add a regression test where `credentialsWrapper` and an existing authorization header are both present.

## Fix Summary

Changed `AbstractGrpcTransport::headersWithCredentials()` to validate `credentialsWrapper` and call `checkUniverseDomain()` whenever the option is present, while still avoiding auth header generation when user metadata already includes authorization.

## Verification

Verified with `composer test`, `composer lint`, and `composer test:coverage`.
