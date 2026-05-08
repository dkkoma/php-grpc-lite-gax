# Review Finding: Expose OK Response Metadata Callback

State: closed
Source: domain model reviewer

## Context

`AbstractGrpcTransport` resolves successful unary responses without invoking GAX `metadataCallback`, even though `UnaryResponse` preserves response metadata.

## Impact

Successful response metadata becomes inaccessible to GAX callers and middleware.

## Proposed Fix

Invoke `options['metadataCallback']` with response metadata before resolving an OK unary response, matching GAX transport behavior.

## Fix Summary

Implemented `metadataCallback` handling in `AbstractGrpcTransport` for OK unary responses and added regression coverage.

## Verification

- `composer lint`
- `composer test`
