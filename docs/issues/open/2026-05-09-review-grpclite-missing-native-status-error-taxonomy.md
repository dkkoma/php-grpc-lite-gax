# GrpcLiteNativeBridge Treats Missing Native Status As Service UNKNOWN

State: open
Source: reviewer finding

## Context

Domain model review finding. `GrpcLiteNativeBridge::statusCode()` returns `GrpcStatusCode::UNKNOWN` when the native event does not contain an integer status code, and `GrpcLiteNativeBridgeTest::testToleratesUnexpectedNativeEventShapes()` locks in that behavior.

## Impact

The repository error taxonomy distinguishes service gRPC statuses from transport/backend failures. A completed unary response should carry a native gRPC status; if the native event has no usable status, that is a bridge or transport failure, not a service-level `UNKNOWN` response. Returning `UNKNOWN` bypasses `AbstractGrpcTransport`'s backend-failure mapping to `UNAVAILABLE`.

## Proposed Fix

Make `GrpcLiteNativeBridge` throw a backend/bridge exception when the receive event lacks a usable status object or integer code. Keep mapping unknown integer status values to `GrpcStatusCode::UNKNOWN`, but treat absent/malformed status as a transport failure.

## Fix Summary

Fill this in when closing the issue. Summarize what changed and where.

## Verification

not run; reviewer finding only.
