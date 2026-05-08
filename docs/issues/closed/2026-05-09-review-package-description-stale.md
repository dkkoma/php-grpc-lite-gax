# Package description omits GrpcLite backend

State: closed
Source: reviewer finding

## Context

`composer.json` still describes the package as "GAX TransportInterface adapter foundation with a FrankenPHP grpc-go backend." The current repository state includes `GrpcLiteBackend`, `GrpcLiteNativeBridge`, `GrpcLiteTransport`, and related tests.

## Impact

The package metadata is now stale and under-describes the installed capabilities. This is minor, but it can mislead contributors and Packagist users about whether `php-grpc-lite` support exists in the package.

## Proposed Fix

Update the Composer description to mention both FrankenPHP grpc-go and `php-grpc-lite` GAX transport adapters, keeping it concise and accurate for the current implementation state.

## Fix Summary

Updated `composer.json` description to mention both FrankenPHP grpc-go and `php-grpc-lite` backends.

## Verification

Fixed in `20763ad`. Verified with `composer validate-project`.
