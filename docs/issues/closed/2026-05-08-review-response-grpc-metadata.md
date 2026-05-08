# Review Finding: Preserve Response grpc-* Metadata

State: closed
Source: domain model reviewer

## Context

`MetadataValidator` rejects every `grpc-*` key and is used by both request and response value objects.

## Impact

Backends cannot preserve response/error metadata such as `grpc-status-details-bin`, which GAX can decode into rich error details.

## Proposed Fix

Split request metadata validation from response metadata validation. Continue rejecting caller-supplied `grpc-*` request metadata, but allow backend-observed response metadata.

## Fix Summary

Split request and response metadata validation. Request metadata still rejects `grpc-*`; response metadata allows backend-observed `grpc-status-details-bin`.

## Verification

- `composer lint`
- `composer test`
