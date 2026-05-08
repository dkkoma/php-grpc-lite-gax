# Review Finding: Decide Response Metadata Shape

State: closed
Source: domain model reviewer design decision

## Context

`UnaryResponse::metadata` currently conflates response headers and trailers into one metadata bag.

## Impact

gRPC headers, trailers, and status details are distinct carriers, and concrete backend mapping may need that distinction later.

## Proposed Fix

Document the current single-metadata-bag decision or split response metadata before concrete backend implementation.

## Fix Summary

Documented the current single metadata bag decision in `docs/design.md`, with a note that concrete backend design may split headers/trailers later if needed.

## Verification

Reviewed `docs/design.md`.
