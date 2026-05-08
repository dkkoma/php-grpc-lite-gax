# GrpcLite metadata callback receives merged trailers

State: open
Source: reviewer finding

## Context

Transport behavior review found that `GrpcLiteNativeBridge::unaryCall()` merges initial response metadata with status/trailing metadata into `GrpcLiteResponse::metadata`. `AbstractGrpcTransport::resolveUnaryResponse()` passes that single bag to `metadataCallback` on successful calls. The stock GAX `GrpcTransport` passes only initial metadata to `metadataCallback` on success and uses status trailing metadata for errors.

## Impact

Successful google-cloud-php calls may expose trailers such as `grpc-status`, `grpc-message`, or `grpc-status-details-bin` through the success metadata callback. This differs from GAX gRPC behavior and makes it hard to preserve error metadata semantics separately from success metadata.

## Proposed Fix

Split backend response metadata into initial metadata and trailing/status metadata before the public transport behavior is stabilized. Preserve initial metadata for success `metadataCallback`, and use trailing/status metadata when constructing non-OK `ApiException`. Add transport tests that compare success callback metadata and non-OK error metadata behavior.

## Fix Summary

Fill this in when closing the issue. Summarize what changed and where.

## Verification

not run; reviewer finding only.
