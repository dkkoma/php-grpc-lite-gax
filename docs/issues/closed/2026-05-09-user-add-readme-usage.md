# Add README Usage Guide

State: closed
Source: user instruction

## Context

The user asked to create a README that presents usage examples for direct google-cloud-php usage, `google/cloud-spanner`, and Laravel applications.

## Impact

Users need a clear entry point for installing the package, choosing the grpc-lite or FrankenPHP transport, and wiring the transport into generated Google Cloud PHP clients without relying on repository tests as examples.

## Proposed Fix

Add `README.md` with installation notes, transport selection guidance, direct Spanner examples, Laravel service-container examples, and smoke-test commands.

## Fix Summary

Added `README.md` with installation notes, grpc-lite and FrankenPHP transport selection, generated Spanner GAPIC examples, Laravel service-container wiring, and smoke-test commands.

## Verification

- `composer verify`
