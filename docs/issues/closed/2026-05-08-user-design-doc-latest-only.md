# User Instruction: Keep Latest Design In Docs

State: closed
Source: user instruction

## Context

Design information should live under `docs/` and only reflect the latest current design.

## Impact

Duplicated design details in contributor instructions can become stale and conflict with the design document.

## Proposed Fix

Move design details out of `AGENTS.md` and into `docs/design.md`.

## Fix Summary

Added `docs/design.md` and removed the architecture overview from `AGENTS.md`. Updated `AGENTS.md` to state that `docs/design.md` is the latest-design source.

## Verification

Reviewed `AGENTS.md` and `docs/design.md` for separation of contributor guidance and design content.
