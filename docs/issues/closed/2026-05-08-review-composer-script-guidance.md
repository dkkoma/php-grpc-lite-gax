# Review Finding: Align Composer Scripts With Guidance

State: closed
Source: test reviewer

## Context

`AGENTS.md` listed `composer cs-fix`, but `composer.json` did not define that script.

## Impact

Contributors following the guide would hit a missing script.

## Proposed Fix

Either add a formatter script or remove the command from contributor guidance.

## Fix Summary

Removed `composer cs-fix` from `AGENTS.md` for now. Formatting tooling can be added later as its own issue.

## Verification

Reviewed `AGENTS.md` against `composer.json`.
