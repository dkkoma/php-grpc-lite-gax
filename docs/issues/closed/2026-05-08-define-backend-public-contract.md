# Define Backend Public Contract

State: closed
Source: work unit

## Context

`UnaryBackend`, request/response DTOs, status model, and `AbstractGrpcTransport` are currently exposed through Composer PSR-4 autoloading.

## Impact

These types can be treated as stable public extension points before concrete backends prove the contract.

## Proposed Fix

Document the backend and abstract transport layer as internal for now. Mark the relevant PHP types with `@internal` until the package has stable concrete backend APIs.

## Fix Summary

Marked the backend and abstract transport types as `@internal` and documented the current public-boundary decision in `docs/design.md`.

## Verification

- `composer lint`
- `composer test`
