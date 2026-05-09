# README Should Lead With ApiEndpoint Rationale

State: closed
Source: user instruction

## Context

The user asked that the README introduction explain the original reason for the
GAX patch experiment: `apiEndpoint` resolution does not reach a prebuilt
`TransportInterface`. The important point is the discovered GAX boundary, not
the patch mechanism itself.

## Impact

Leading with the patch can make the experiment look like an intended
installation strategy. Leading with the `apiEndpoint` discovery makes the
archive rationale clearer and explains why the patch branch exists.

## Proposed Fix

Rewrite the README opening note so it explains the `apiEndpoint` problem first,
then states that the patch approach was explored and archived as operationally
heavy.

## Fix Summary

Updated the README opening note to lead with the discovered `apiEndpoint`
boundary: prebuilt `TransportInterface` objects do not receive the endpoint
resolved by google-cloud-php/GAX, which motivated the archived GAX patch
experiment.

## Verification

Documentation-only change. `composer verify` had passed after reverting the
patch implementation; no additional code verification was needed for this note.
