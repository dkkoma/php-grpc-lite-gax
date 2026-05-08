# Review Finding: Preserve Metadata On Case Collisions

State: closed
Source: domain model reviewer

## Context

`AbstractGrpcTransport` lowercases metadata names but overwrites earlier values when headers differ only by case.

## Impact

Auth, routing, or request-parameter metadata can be silently lost.

## Proposed Fix

Merge metadata value lists after lowercasing header names.

## Fix Summary

Changed request metadata normalization to merge values after lowercasing header names instead of overwriting existing values.

## Verification

- `composer lint`
- `composer test`
