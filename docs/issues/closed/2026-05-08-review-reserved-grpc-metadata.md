# Review Finding: Reject Reserved grpc-* Metadata

State: closed
Source: domain model reviewer

## Context

Metadata validation accepts `grpc-*` names such as `grpc-timeout`, `grpc-status`, and `grpc-message`.

## Impact

Caller metadata can collide with protocol-controlled timeout/status fields.

## Proposed Fix

Reject reserved `grpc-*` metadata names at the shared metadata boundary and add tests.

## Fix Summary

Rejected `grpc-*` metadata names in `MetadataValidator` and transport header normalization, with request and transport tests.

## Verification

- `composer lint`
- `composer test`
