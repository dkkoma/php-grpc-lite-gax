# Add FakeBackend and TransportInterface Path

State: closed
Source: work unit

## Context

Implement the first unary transport slice for the Composer library. The intended design is `TransportInterface` -> `AbstractGrpcTransport` -> `UnaryBackend` -> backend implementation, with `FakeBackend` used by repository tests.

## Impact

Without this slice, backend tests would not exercise the actual GAX transport boundary, and the package would not prove that `google/gax` calls can be converted into backend requests.

## Proposed Fix

Add Composer package setup, `google/gax`, `AbstractGrpcTransport`, unary backend request/response types, `FakeBackend`, PHPUnit tests, and PHPStan level max.

## Fix Summary

Implemented `AbstractGrpcTransport` for unary calls, explicit unsupported streaming methods, `UnaryBackend`, `UnaryRequest`, `UnaryResponse`, `GrpcStatusCode`, and test-only `FakeBackend`. Added `docs/design.md`, PHPUnit, and PHPStan configuration.

## Verification

Verified in Docker PHP 8.4 container:

- `composer validate --strict`
- `composer lint`
- `composer test`
