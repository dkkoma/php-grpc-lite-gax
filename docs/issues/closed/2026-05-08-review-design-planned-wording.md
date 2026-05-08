# Review Finding: Clarify Current And Planned Backends

State: closed
Source: domain model reviewer

## Context

`docs/design.md` says planned implementations include `FrankenGrpcBackend` and `FakeBackend`, though both now exist.

## Impact

Contributors can misread implemented backends as future work.

## Proposed Fix

Reword the backend list to distinguish current implementations from planned `GrpcLiteBackend`.

## Fix Summary

Updated `docs/design.md` to distinguish current `FrankenGrpcBackend` and `FakeBackend` from planned `GrpcLiteBackend`.

## Verification

Reviewed `docs/design.md`.
