# Review Finding: Validate Metadata Keys

State: closed
Source: domain model reviewer

## Context

Metadata names only rejected empty strings.

## Impact

Invalid gRPC metadata names could fail later and differently across concrete backends.

## Proposed Fix

Validate shared metadata names at the backend boundary.

## Fix Summary

Updated `MetadataValidator` to require lowercase gRPC metadata characters and normalized transport headers to lowercase.

## Verification

- `composer lint`
- `composer test`
