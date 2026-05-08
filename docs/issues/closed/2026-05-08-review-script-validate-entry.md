# Review Finding: Add Explicit Composer Validate Script

State: closed
Source: test reviewer

## Context

`AGENTS.md` lists `composer validate` as a stable verification command, but `composer.json` does not define a project script for it.

## Impact

The project-owned script surface does not mirror contributor guidance.

## Proposed Fix

Add an explicit project validation script that runs `composer validate --strict`.

## Fix Summary

Added `composer validate-project` and updated `AGENTS.md` to reference it.

## Verification

- `composer validate-project`
