# Add FrankenPHP Extension API Spec

State: closed
Source: user instruction

## Context

The user wants this repository to provide the design and requirements for a separate FrankenPHP extension repository.

## Proposed Fix

Add a dedicated design/specification document for the FrankenPHP extension API bridge, covering unary and server streaming contracts, byte-level request/response boundaries, metadata, deadlines, status details, cancellation, lifecycle, and compliance expectations.

## Fix Summary

Added `docs/frankenphp-extension-api.md` and linked it from the current design document.

## Verification

- `docker run --rm -v /Users/daisuke/src/php-grpc-lite-gax:/workspace -w /workspace php-grpc-lite-gax-dev:latest composer verify`
