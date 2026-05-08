# Review Finding: Document UnaryBackend Contract On Interface

State: closed
Source: domain model reviewer

## Context

`UnaryBackend` lifecycle and error semantics are in design docs and tests, but not on the interface methods.

## Impact

Future backend implementers can miss required post-close and failure semantics.

## Proposed Fix

Add concise PHPDoc to `UnaryBackend::call()` and `close()`.

## Fix Summary

Added PHPDoc to `UnaryBackend::call()` and `close()` documenting response, failure, and lifecycle semantics.

## Verification

Reviewed `src/Backend/UnaryBackend.php`.
