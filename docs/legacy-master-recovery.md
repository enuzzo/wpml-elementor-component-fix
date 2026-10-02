# Existing translated masters with legacy origin data

## Observed boundary

A read-only audit after the 1.0.4 release found an inherited numeric heading missing in two translated pages, while explicit overrides rendered. A private native backup showed a modern `escaped-html` origin in the source master and one target, but `html-v3` origins in two older translated masters. Their raw numeric text was still present. The site session reported a null resolved heading for those older targets. This supports a storage/resolution mismatch as the next diagnosis; it does not establish when or which operation created it.

The two accepted fresh-job cycles in the [1.0.4 release record](releases/1.0.4.md) used newly translated test masters. Their success does not certify recovery of every existing translated master. The production-page baseline checks in that record were also not an exhaustive audit of inherited text. Do not infer a new regression from discovery alone. A subsequent source-master job recovered the affected rendered headings, as recorded below; that targeted result does not establish universal legacy recovery.

For illustration only, these are invented origins with the same visible number:

```json
{"$$type":"escaped-html","value":"7"}
```

```json
{"$$type":"html-v3","value":{"content":{"$$type":"string","value":"7"},"children":[]}}
```

Equal text does not imply equal typed structures or equal rendering. The installed native WPML node handler can address a legacy origin's `value.content.value`; it does not convert that origin to `escaped-html` merely by importing text. The adapter deliberately accepts only modern string-valued `escaped-html` origins. Activation neither scans nor migrates previously saved destinations.

## Subsequent live recovery — 2026-10-02

With the unchanged 1.0.4 package active, the separately authorized site session created fresh source-master jobs for English, French and German, exported XLIFF 1.2, reviewed only translation targets and imported through WPML. No direct destination edits were reported.

| Evidence | Result and limit |
| --- | --- |
| Archived XLIFF | Each language contains three units and no numeric source unit. The missing heading's literal `"1"` is absent. The site session attributes this to native numeric filtering; these files establish the omission, not the exact filtering stage. |
| Offline package validation | Zero errors and zero unreviewed warnings. This validates the package, not the import or rendering. |
| Native import | Reported completed by the site session. Subsequent resolved reads of the two affected masters report `title = "1"`, previously null. |
| Archived anonymous HTML | Read-only inspection independently confirms numeric headings `1, 2, 3, 4` in both affected language homepages. |
| Raw persisted type | The supplied after-import evidence contains resolved reads and rendered HTML, not a new raw serialized target export. The precise post-import origin type and the full structural diff are not independently asserted here. |

This demonstrates functional recovery of these previously translated masters through a fresh native job even without a numeric XLIFF unit. It does not prove that an individual string import converted the old origin, identify every internal persistence step, or establish recovery without the adapter. The node-level regression remains accurate: the adapter itself does not migrate a legacy node. The complete job has a wider responsibility than that single callback.

The earlier guide incorrectly required the numeric unit in every successful recovery export. That requirement is superseded by the observed result. Distinguish legitimate numeric omission from missing expected prose; do not manufacture a unit or alter the number to make it translatable. This result for `"1"` does not establish live behavior for `"0"` or all numeric formats.

## Recovery through WPML, subject to site authorization

Use the normal [WPML translation dashboard](https://wpml.org/documentation/translating-your-contents/translation-dashboard/) and [XLIFF workflow](https://wpml.org/documentation/translating-your-contents/using-desktop-cat-tools/configuring-xliff-file-options/). These official workflows establish how to dispatch and import jobs, not a guarantee that a particular legacy target will be rebuilt. UI labels vary by installed WPML version.

1. Read the site's rules and obtain its separate authorization for production translation work. Preserve verified local backups of the source master, existing target masters, relevant metadata, translations and affected page rendering. Record the source-to-target language relationships and explicit versus inherited bindings.
2. Confirm from persisted source data that the intended heading/paragraph has a direct string-valued `escaped-html` origin, with its expected key and element identity. Do not convert a target, manufacture an instance override or change a number just to trigger a job.
3. With 1.0.4 active, send the **source master** through a fresh WPML job for each affected language using the installed dashboard's supported retranslation procedure. A page-only job may not rebuild its referenced master. Do not reuse an old XLIFF or assume its package contains newly supported fields. If a fresh job cannot be created, stop and investigate that workflow instead of manually editing job records.
4. Inspect the newly exported XLIFF before importing. Require all expected translatable prose with native identities and record numeric omissions separately. The successful observed recovery omits the literal `"1"`; absence of that numeric unit alone is not a failed gate. If expected prose is missing or only the document title remains when prose should be present, stop. When a number does have a unit, an identical source/target can be legitimate: review that equality and preserve the value. Never fabricate missing units, alter the numeric source to force extraction, or assume the known native `"0"` limitation is fixed.
5. Review every exported target, retaining previously approved translations where still appropriate; a master job can update more than the missing number. Import through WPML only. The observed full job recovered the resolved numeric heading and inherited rendering, but the adapter's node-level test does not guarantee how every installed job pipeline rebuilds existing target data.
6. Read the saved targets again. Require the intended modern origin shape, numeric text, correct element/key associations and language mapping, with no fabricated overrides, temporary IDs or unrelated changes. Compare the full element trees and relevant registry metadata; do not infer success from an accepted import or a deserialized text value alone.
7. Check the affected inherited instance and the explicit-override controls in each target language, desktop/mobile and unauthenticated rendering. Keep master recovery distinct from page retranslation. If a separate page job is needed, diagnose and authorize it under the site's workflow.

If native import leaves the page empty or saved data inconsistent, preserve the evidence and stop, whether or not the number had an XLIFF unit. Investigate the native job input, mapping and import path; do not add a target migration or direct database repair to force acceptance. Report rendered recovery separately from raw structural verification. The observed functional recovery above does not waive those checks for another master or stack.

## Regression evidence

The public `legacy-source` scenario uses invented data. It verifies that a nonzero numeric modern origin is extracted with native identity, a same-value import retains the complete modern structure, source edits retain identity, and processing the source never mutates a separate legacy target. Passing a legacy node to the adapter yields no migration; the native synthetic handler keeps its legacy type.

This is a node-contract test, not a simulation of WPML's job builder, existing-target replacement, persistence or Elementor rendering. The current development suite has 45 isolated scenarios; the published 1.0.4 release was validated with 44. The separate site session subsequently completed the live recovery described above. This repository session only inspected archived evidence; it performed no live operations. Runtime and published ZIP remain unchanged.
