# User Instruction: One Issue Per Instruction And Finding

State: closed
Source: user instruction

## Context

User instructions, work units, and reviewer findings must be tracked individually rather than aggregated.

## Impact

Aggregated issue records hide whether each instruction or review finding was fixed and verified.

## Proposed Fix

Clarify issue tracking rules and split reviewer findings into individual issue files.

## Fix Summary

Updated `AGENTS.md` to require one issue file per user instruction, work unit, and reviewer finding. Replaced the aggregate reviewer issue with individual closed issue records.

## Verification

Confirmed individual issue files exist under `docs/issues/closed/`.
