# Archive TransportInterface Experiment

State: closed
Source: user instruction

## Context

The user concluded that the GAX Composer patch strategy is technically useful
but operationally heavy for application users. The patch implementation should
be preserved on a separate branch, while the main branch should return to the
pre-patch implementation and keep a concise experiment summary.

## Impact

Keeping the patch path as the main documented integration would imply GAX
version pinning and Composer patch operations for users. That is too much
operational weight for the likely benchmark and exploration use case.

## Proposed Fix

Create a separate branch for the GAX `transportFactory` patch implementation.
Revert the patch implementation from the main branch. Add a design/result note
summarizing the TransportInterface experiment outcome and the recommended next
direction.

## Fix Summary

Created `experiment/gax-transport-factory-patch` at the completed patch
implementation. Reverted the patch implementation from `master` with commit
`4ab9f46`. Added `docs/transport-interface-experiment.md` summarizing that the
TransportInterface path works technically but should remain experimental, while
benchmark/runtime switching should move below the `Grpc\Channel` /
`Grpc\Call` compatible layer. Added a short README note linking to the summary.

## Verification

`composer verify` passed in the PHP 8.4 dev container after reverting the patch
implementation.
