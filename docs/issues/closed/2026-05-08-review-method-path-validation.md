# Review Finding: Validate Service And Method Path Parts

State: closed
Source: domain model reviewer

## Context

`UnaryRequest::path()` could build invalid paths because service and method only rejected empty strings.

## Impact

Malformed method names could produce ambiguous backend paths.

## Proposed Fix

Validate protobuf service and method names before deriving the path.

## Fix Summary

Added service and method regex validation in `UnaryRequest` and added tests for invalid method names.

## Verification

- `composer lint`
- `composer test`
