# Composer Metadata Overstates GAX Patch Version Range

State: closed
Source: reviewer finding

## Context

The README and design document state that
`patches/google-gax-transport-factory.patch` targets `google/gax` 1.42.3, and
the README patch example pins `"google/gax": "1.42.3"`. The package metadata in
`composer.json` requires `"google/gax": "^1.42.3"`, which allows later GAX
versions even though the bundled patch is version-specific.

## Impact

Applications following the top-level install command can resolve a newer GAX
version while the advertised factory integration still depends on a patch
written for 1.42.3. That creates avoidable setup ambiguity: users may see patch
application failures or assume the `transportFactory` patch is supported across
the whole Composer range.

## Proposed Fix

Make the Composer metadata and README version guidance agree. Options include
pinning `google/gax` to `1.42.3` while the patch is required for the main usage
path, or keeping the broad runtime range but making the metadata/suggestion and
install command explicitly say that `transportFactory` users must pin a
patch-verified GAX version.

## Fix Summary

Clarified README version guidance: low-level APIs support the Composer `google/gax` range, while the recommended `transportFactory` integration is patch-verified against `google/gax` 1.42.3 and application patch setup should pin that version.

## Verification

Verified with `composer validate-project` and `composer verify` in the PHP 8.4 dev container.
