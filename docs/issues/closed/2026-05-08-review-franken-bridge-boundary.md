# Review Finding: Decide Franken Bridge Result Boundary

State: closed
Source: domain model reviewer design decision

## Context

The design says `FrankenGrpcBackend` owns bridge result validation, but `FrankenGrpcBridge` already returns a domain-shaped `FrankenGrpcResponse`.

## Impact

The actual grpc-go adapter boundary is ambiguous.

## Proposed Fix

Document that `FrankenGrpcBridge` is responsible for normalizing grpc-go output into `FrankenGrpcResponse`, while `FrankenGrpcBackend` maps that domain-shaped bridge response into `UnaryResponse`.

## Fix Summary

Documented that `FrankenGrpcBridge` normalizes grpc-go output into `FrankenGrpcResponse`, while `FrankenGrpcBackend` maps that domain-shaped response into `UnaryResponse`.

## Verification

Reviewed `docs/design.md`.
