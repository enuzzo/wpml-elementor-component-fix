# Typed select options — 1.0.4 candidate

The candidate retains the component override, master-origin and form-name filters from 1.0.3 without changes. It adds a narrow adapter for the existing native `e-form-select` option-label registration. It does not synchronize component metadata or extend every form control/action.

## Why registration alone is insufficient

The [public WPML configuration](https://cdn.wpml.org/wpml-config/elementor/wpml-config.xml) registers collection `options>value` and visible label `value>key>value`. The observed Elementor shape uses typed option rows, each containing a visible `key` and a separate technical `value`.

The installed native item handler supplied during integration work reads its collection as a literal settings key and derives string names from an item `_id`. Typed rows have neither the literal collection key nor that item ID. An isolated diagnostic confirmed that exposing the collection alone makes different options share an identity and can import the second label into the first row. Its truthiness check also omits the visible label `"0"`. See [the integration diagnosis](integration-gaps.md) for evidence and unresolved metadata questions.

## Behavior and boundaries

The registration guard requires the recognized select widget, original ordinary fields, and exactly the known option-label item registration with its native label/editor metadata. Existing custom integrations, alternate paths, additional item registrations and unknown native method signatures stay unchanged. No missing widget or missing item registration is fabricated.

The module composes the installed native item handler. Its runtime probes check plain text, markup and `"0"`, distinct identities, and exact item updates. If native support works, it forwards unmodified data to that handler. Expected missing-collection diagnostics count as missing support; other exceptions leave the decision unset. The caller's PHP error handler is restored in all cases, and any native active-settings cache is primed with the actual element before probing.

When normalization is necessary:

- Require a sequential typed options list with string labels and unique, nonempty string technical values, without pre-existing item IDs or a literal collection alias. Unknown or ambiguous shapes are not normalized.
- In a copy, expose the collection under its registered literal key and supply each row an `_id` consisting of a fixed prefix plus the first 24 hexadecimal characters of its technical value's SHA-256. WPML generates the final string name. Labels and row positions do not determine identity; duplicate labels remain distinct after reordering.
- Let native extraction read a temporary nonempty placeholder for `"0"`, then restore the exact zero value on the native-named string. Choose a placeholder absent from the real labels. Blank labels stay omitted, matching the observed source rendering. These select semantics do not change the master's native empty/zero limitations.
- Require distinct returned identities for both extraction and import. Any collision, including one caused by a custom naming filter, rejects adaptation. Validate the imported item against the expected copy with only its visible label changed, remove the temporary `_id`, and return the item for WPML's normal nested insertion.

Technical values, types, item metadata and the source are preserved. Temporary aliases, IDs and placeholders are never returned as saved data. The adapter writes no database metadata or translated post directly. Changes to a technical value change its surrogate identity; generate fresh jobs after such source changes or a switch to native support. Existing exports do not gain missing labels retroactively.

## Validation

The public suite contains **44 isolated scenarios**, including 12 select scenarios using original synthetic doubles. They cover missing/working/partial native support, broken imports, errors, unknown APIs/configurations, colliding names, duplicate labels, zero/blank values, metadata preservation, repeat import, source edits, reordering and coexistence.

A separate private local diagnostic passes **20 checks** on the installed native item classes and outer node dispatcher with environment stubs. It verifies four visible labels from five rows, distinct native identities, exact zero handling, correct nested import, stable reordering/source-edit cycles, rejection of duplicate technical values, and unchanged source/technical metadata. The existing **28-check master diagnostic** also passes. No vendor code or client fixtures are distributed in this repository. These local checks do not establish a WordPress job/render cycle.

**First live export observation — 2026-10-02:** the site session reported 1.0.4 active alone. Read-only inspection of its archived English/French/German demo XLIFF files confirms 64 units per file and all three human-readable option labels with distinct IDs, including the repeated label. No literal `"0"` unit is present. The three forwarded-wrapper exports contain only their document title, consistent with the previously recorded null-reference fixture. A downstream falsey filter is an unverified hypothesis. The site session subsequently reported successful native import and 168 error-free DOM comparisons across desktop/mobile and unauthenticated views, including explicit, inherited, partial and nested cases. The three human labels are translated into the correct technical-value rows, and zero stays unchanged in data and rendering despite having no XLIFF unit. No additional zero fix is justified by this observed result.

**Fixture boundary:** the five-row fixture in local synthetic/native-source diagnostics includes an empty label. In the live attempt, Elementor removes that submitted row during normal source saving/sanitization, before WPML; a fresh source read confirms four persisted rows. Thus the live result demonstrates native source omission of a blank option, not WPML extraction/import of a persisted fifth row. Keep attempted input, persisted source, XLIFF units and rendered options separate when reporting coverage.

Registry metadata remains absent, but the site session found no resulting frontend defect in this fixture. The second cycle after source edits and option reordering remains pending; the first result does not certify untested component/editor workflows.

## Site acceptance

Frozen local artifact: `netmilk-wpml-component-compat-1.0.4.zip` (16,080 bytes), SHA-256 `95fc63ddb72778105a677fbf1fb2e56dab5460af591bb136b51d69c34602b50f`. A checksum-addressed copy and its checksum file are preserved under ignored `dist/candidates/1.0.4/<SHA-256>/`. This is a test candidate, not a published release.

1. Preserve the frozen 1.0.3 package and verify the new ZIP's checksum. Capture existing translations before the controlled update; inspect them after activation before importing anything. Keep other compatibility adapters/addons inactive during this candidate test and retain rollback.
2. Confirm that the effective select registration matches the known native rule. If the expected labels remain absent, inspect installed/cached configuration and site overrides; the plugin deliberately does not invent a missing registration.
3. Generate fresh master and page jobs. Expect existing master-origin texts and distinct identities for three human-readable labels: two identical labels with distinct technical values and a different label. Submit `"0"` and a blank label as separate controls, then read the persisted source before export. Record any row removed by Elementor at save time; do not count it as a persisted WPML test case. Record whether zero has an XLIFF unit; if omitted, verify its unchanged data/rendering through native import instead of manufacturing a unit. Blank labels remain omitted. Check unchanged technical values.
4. Translate only XLIFF targets and import through WPML. Compare raw option data before/after: only label leaves may change, with no `_id` or literal alias added. Verify the two identically named source options independently.
5. Change a source label and reorder options, then repeat with fresh jobs. Confirm stable identities for unchanged technical values and correct destination rows. Verify source immutability, form names and existing component overrides.
6. Inspect actual inherited/overridden rendering, links and forms in desktop/mobile and unauthenticated contexts. Registry absence alone does not establish frontend failure; record metadata consistency separately. Do not repair targets manually to pass this gate.

The first 1.0.4 site cycle is reported successful on the tested fixture. Final acceptance and release remain pending the second source-edit/reordering cycle. The registry investigation continues only if further rendering/metadata findings require it; the candidate makes no speculative registry writes.
