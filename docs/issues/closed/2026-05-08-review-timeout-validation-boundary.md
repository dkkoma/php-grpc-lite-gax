# Review Finding: Validate Positive Timeout At GAX Boundary

State: closed
Source: domain model reviewer

## Context

`timeoutMillis` values of zero or less can pass transport type validation and fail later inside `UnaryRequest`.

## Impact

Backend value-object exceptions leak to GAX callers.

## Proposed Fix

Validate positive timeout values inside `AbstractGrpcTransport::timeoutSeconds()` and throw `ValidationException`.

## Fix Summary

Added positive timeout validation to `AbstractGrpcTransport::timeoutSeconds()` so non-positive values raise `ValidationException`.

## Verification

- `composer test`
