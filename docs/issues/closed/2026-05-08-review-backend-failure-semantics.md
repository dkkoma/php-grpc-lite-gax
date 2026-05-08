# Review Finding: Own Backend Failure Semantics

State: closed
Source: domain model reviewer

## Context

The design says transport failures without gRPC status should become `UNAVAILABLE`, but backend throwables are currently rejected unchanged.

## Impact

Concrete backend exceptions could leak through the GAX transport layer.

## Proposed Fix

Document and implement `AbstractGrpcTransport` ownership of backend throwable to `ApiException` mapping.

## Fix Summary

Documented that `AbstractGrpcTransport` owns backend throwable mapping and implemented conversion to GAX `ApiException` with `GrpcStatusCode::UNAVAILABLE`.

## Verification

- `composer lint`
- `composer test`
