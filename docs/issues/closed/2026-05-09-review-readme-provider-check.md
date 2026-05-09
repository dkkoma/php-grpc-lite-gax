# README Provider Check

State: closed
Source: reviewer finding

## Context

Reviewers found that `php -m | grep -x grpc` only proves that some `grpc` module is loaded and does not distinguish `php-grpc-lite` from official `ext-grpc`.

## Impact

Users can accidentally validate the wrong runtime provider for `GrpcLiteTransport`.

## Proposed Fix

Add a provider-specific verification command that checks the loaded module and `Grpc\VERSION`, and state that official `ext-grpc` is not a valid provider for `GrpcLiteTransport`.

## Fix Summary

README now supplements `php -m` with a `Grpc\VERSION` check and explicitly states that official `ext-grpc` is not a valid provider for `GrpcLiteTransport`.

## Verification

- Clean `php:8.4-cli-trixie` container provider check: `extension_loaded("grpc")`, `defined("Grpc\\VERSION")`, and `Grpc\VERSION`
- `composer verify`
