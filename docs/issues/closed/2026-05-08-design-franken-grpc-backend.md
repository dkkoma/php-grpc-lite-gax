# Design FrankenGrpcBackend

State: closed
Source: work unit

## Context

`FrankenGrpcBackend` is planned as the FrankenPHP grpc-go bridge, but its request, metadata, status, deadline, and lifecycle mapping are not documented.

## Impact

Implementing the backend without a design note risks coupling backend details to GAX transport internals.

## Proposed Fix

Update `docs/design.md` with the latest intended `FrankenGrpcBackend` responsibilities and mapping rules.

## Fix Summary

Updated `docs/design.md` with the current `FrankenGrpcBackend` responsibility, request mapping, response mapping, status mapping, and lifecycle boundary.

## Verification

Reviewed `docs/design.md`.
