# Existing translated masters with legacy origin data

## Observed boundary

A read-only audit after the 1.0.4 release found an inherited numeric heading missing in two translated pages, while explicit overrides rendered. A private native backup showed a modern `escaped-html` origin in the source master and one target, but `html-v3` origins in two older translated masters. Their raw numeric text was still present. The site session reported a null resolved heading for those older targets. This supports a storage/resolution mismatch as the next diagnosis; it does not establish when or which operation created it.

The two accepted fresh-job cycles in the [1.0.4 release record](releases/1.0.4.md) used newly translated test masters. Their success does not certify recovery of every existing translated master. The production-page baseline checks in that record were also not an exhaustive audit of inherited text. Do not infer a new regression from discovery alone, or report the legacy issue as repaired.

For illustration only, these are invented origins with the same visible number:

```json
{"$$type":"escaped-html","value":"7"}
```

```json
{"$$type":"html-v3","value":{"content":{"$$type":"string","value":"7"},"children":[]}}
```

Equal text does not imply equal typed structures or equal rendering. The installed native WPML node handler can address a legacy origin's `value.content.value`; it does not convert that origin to `escaped-html` merely by importing text. The adapter deliberately accepts only modern string-valued `escaped-html` origins. Activation neither scans nor migrates previously saved destinations.

## Recovery through WPML, subject to site authorization

Use the normal [WPML translation dashboard](https://wpml.org/documentation/translating-your-contents/translation-dashboard/) and [XLIFF workflow](https://wpml.org/documentation/translating-your-contents/using-desktop-cat-tools/configuring-xliff-file-options/). These official workflows establish how to dispatch and import jobs, not a guarantee that a particular legacy target will be rebuilt. UI labels vary by installed WPML version.

1. Read the site's rules and obtain its separate authorization for production translation work. Preserve verified local backups of the source master, existing target masters, relevant metadata, translations and affected page rendering. Record the source-to-target language relationships and explicit versus inherited bindings.
2. Confirm from persisted source data that the intended heading/paragraph has a direct string-valued `escaped-html` origin, with its expected key and element identity. Do not convert a target, manufacture an instance override or change a number just to trigger a job.
3. With 1.0.4 active, send the **source master** through a fresh WPML job for each affected language using the installed dashboard's supported retranslation procedure. A page-only job may not rebuild its referenced master. Do not reuse an old XLIFF or assume its package contains newly supported fields. If a fresh job cannot be created, stop and investigate that workflow instead of manually editing job records.
4. Inspect the newly exported XLIFF before importing. Require the numeric origin and all expected master strings with their native identities; a title-only export fails the gate. A language-neutral number can legitimately have an identical source and target. Review that equality explicitly and preserve the numeric value. Do not add missing units manually or assume that the known native `"0"` omission is fixed.
5. Review every exported target, retaining previously approved translations where still appropriate; a master job can update more than the missing number. Import through WPML only. Whether this installed job pipeline rebuilds from the modern source or retains the old target structure is the unresolved integration gate, not a promise of the adapter's node-level test.
6. Read the saved targets again. Require the intended modern origin shape, numeric text, correct element/key associations and language mapping, with no fabricated overrides, temporary IDs or unrelated changes. Compare the full element trees and relevant registry metadata; do not infer success from an accepted import or a deserialized text value alone.
7. Check the affected inherited instance and the explicit-override controls in each target language, desktop/mobile and unauthenticated rendering. Keep master recovery distinct from page retranslation. If a separate page job is needed, diagnose and authorize it under the site's workflow.

If the fresh master export contains the number but native import retains the legacy shape or the page remains empty, preserve the evidence and stop. Investigate the native job input, mapping and import path; do not add a target migration or direct database repair to force acceptance. Recovery remains unverified until both persisted data and rendering pass.

## Regression evidence

The public `legacy-source` scenario uses invented data. It verifies that a nonzero numeric modern origin is extracted with native identity, a same-value import retains the complete modern structure, source edits retain identity, and processing the source never mutates a separate legacy target. Passing a legacy node to the adapter yields no migration; the native synthetic handler keeps its legacy type.

This is a node-contract test, not a simulation of WPML's job builder, existing-target replacement, persistence or Elementor rendering. The current development suite has 45 isolated scenarios; the published 1.0.4 release was validated with 44. No production recovery job was exported/imported by this investigation, and the runtime and published ZIP remain unchanged.
