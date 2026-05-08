# Review Finding: Validate GAX Method Syntax At Transport Boundary

State: closed
Source: test reviewer

## Context

`AbstractGrpcTransport::splitMethod()` accepts method strings with extra slashes, allowing backend `InvalidArgumentException` to leak.

## Impact

GAX callers receive inconsistent validation exceptions for malformed method strings.

## Proposed Fix

Require exactly one slash and validate service/method syntax in `AbstractGrpcTransport` before constructing `UnaryRequest`.

## Fix Summary

Updated `AbstractGrpcTransport::splitMethod()` to require exactly one slash and validate service/method tokens before constructing `UnaryRequest`.

## Verification

- `composer lint`
- `composer test`
