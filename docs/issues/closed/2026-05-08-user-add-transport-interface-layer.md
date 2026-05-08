# User Instruction: Add TransportInterface Layer

State: closed
Source: user instruction

## Context

The user pointed out that the initial FakeBackend work missed the core GAX `TransportInterface` layer.

## Impact

The adapter library would not validate its primary integration point with `google/gax`.

## Proposed Fix

Add `google/gax` and implement the GAX unary transport layer above `UnaryBackend`.

## Fix Summary

Added `AbstractGrpcTransport implements TransportInterface`, including unary call conversion, unsupported streaming methods, response decoding, and non-OK error mapping.

## Verification

- `composer validate --strict`
- `composer lint`
- `composer test`
