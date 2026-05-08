# Review Finding: Clarify Package Description Roadmap

State: closed
Source: test reviewer design decision

## Context

Package metadata advertises both FrankenPHP grpc-go and `php-grpc-lite`, while `GrpcLiteBackend` is design-only.

## Impact

The package description can imply shipped support that is not yet implemented.

## Proposed Fix

Narrow `composer.json` description or explicitly document `GrpcLiteBackend` as planned.

## Fix Summary

Narrowed `composer.json` description to the current FrankenPHP backend support and documented `GrpcLiteBackend` as planned/design-only in `docs/design.md`.

## Verification

- `composer validate-project`
