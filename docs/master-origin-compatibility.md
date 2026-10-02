# Direct exposed master text — 1.0.3 candidate

## Evidence and diagnosis

A separate integration session reported that a synthetic master with an exposed heading and paragraph produced only its document-title translation unit in three fresh target-language XLIFF jobs with 1.0.2 alone. An independently created equivalent master and three new jobs reproduced the omission with the earlier adapter alone. No master import was performed. This supports a pre-existing gap, not a regression introduced by 1.0.2. A review of the verified earlier adapter backup confirmed that it handles form names and explicit instance overrides, without a master-origin adapter.

Native WXR backup inspection subsequently confirmed direct origins in the persisted element tree, not just the deserialized property registry. An original, non-client fixture of this shape is:

```json
{
  "id": "synthetic-master-heading",
  "widgetType": "e-heading",
  "settings": {
    "title": {
      "$$type": "overridable",
      "value": {
        "override_key": "synthetic-heading",
        "origin_value": {
          "$$type": "escaped-html",
          "value": "Synthetic <strong>master</strong> &amp; café"
        }
      }
    }
  }
}
```

The [public WPML Elementor configuration](https://cdn.wpml.org/wpml-config/elementor/wpml-config.xml), inspected on 2026-10-02, registers ordinary heading/paragraph fields with alternative modern/legacy paths sharing native identities. Inspection of the installed native node handler established that it already resolves `overridable.value.origin_value` for `string` and `html-v3`, in both extraction and update. The missing case is the `escaped-html` type. Its factory uses that same node handler for string registration and translation updates.

[Elementor's component model](https://github.com/elementor/elementor/blob/4.3.3/modules/components/documents/component.php) and [overridable transformer](https://github.com/elementor/elementor/blob/4.3.3/modules/components/transformers/overridable-transformer.php) explain the distinction between direct origins and inherited instance values. The observed nested fixture uses an `override` reference with a null `override_value` inside the exposed wrapper. That null represents valid inheritance; it is not a literal empty text to replace.

No proprietary source, client fixture, raw backup or identifying site data is included in this public repository.

## Native delegation

The candidate attaches a supplemental integration only to recognized `e-heading`/`e-paragraph` configurations. It keeps their original fields and delegates to a fresh `WPML_Elementor_Translatable_Nodes` instance scoped to those text fields. A request-local guard supplies the scoped configuration during initialization and prevents recursive registration. It does not subclass or replace the native node handler or add competing origin paths with the same identity.

The native public method signatures are checked before registering the module. Missing or incompatible contracts, custom integrations, repeaters, unknown metadata, existing origin paths, duplicate paths and ambiguous identities leave the widget configuration unchanged. Repeat filtering is idempotent.

At runtime:

1. Handle only a direct `overridable` wrapper with a nonempty override key and a string-valued `escaped-html` origin.
2. Check native plain-text and HTML extraction/import using in-memory copies in the actual element context. Run these probes from the native integration callback, after ordinary field processing. Probing a fabricated widget during registration could seed WPML's active-settings cache with the wrong element.
3. If both round trips succeed, return incoming strings/no supplemental update and leave native processing in charge. Cache completed decisions by widget/configuration for the request. Exceptions leave the decision unset and do not adapt data.
4. When native support is missing, change the origin type to `string` in a copy and call native extraction. Accept only strings with native expected identities and the exact source value; append no identity already present. Native labels/editor metadata stay on the returned objects.
5. For import, call the native update on the compatible copy. Accept it only if the entire result equals that copy with just the origin's text changed. Restore `escaped-html`, then return the updated item for WPML's normal integration dispatch to apply.

Native active-field rules remain authoritative. The plugin does not flatten master elements, generate its own field IDs, write the registry, remap masters or materialize instance overrides. Component override and form-name filters remain unchanged from 1.0.2.

## Validation and limits

| Layer | Result and limit |
| --- | --- |
| Public original test doubles | 32 isolated scenarios in total, including 12 master-origin scenarios; no vendor implementation or CMS is loaded |
| Private isolated native-source diagnostic | 28 checks passed locally on the installed node-handler source with original environment stubs: baseline omission, delegated extraction/import, exact text/type/metadata preservation, identity, link preservation, active fields/cache, falsey values and forwarded references |
| Fresh master-job extraction on the site | Reported passed for the frozen 1.0.3 candidate alone in English, French and German; three units per XLIFF, including both direct origins, no duplicates |
| Native master import | Reported successful; read-only XLIFF/WXR comparison confirms each target tree differs only in the two translated origin values |
| Property registry and full rendering acceptance | Dedicated registry missing in translated masters; inherited rendering and full migration acceptance remain unresolved |

On 2026-10-02, the separate integration session reported that the candidate with SHA-256 `8d40233c3e0b57da28de022fdfd940e5c244074c37cdee5e8fd2fa0222a105b2` was active alone, with the earlier adapter and form addon confirmed inactive. Fresh XLIFF 1.2 exports in all three target languages contained the document title, direct escaped-html heading origin and paragraph origin, preserving strong markup and the entity. Native master import subsequently completed. Read-only comparison of the archived targets and WXR element trees confirmed the two translated values with unchanged types, keys, node IDs and other element data.

The dedicated component registry is absent from the translated masters despite copied page settings. An extended page export also omitted select labels, so those page jobs were not imported and the site session reported rollback. See [the separate metadata/select investigation](integration-gaps.md); neither gap is repaired by 1.0.3. Raw evidence and job identifiers remain in the site's private storage.

The subsequent 1.0.4 site session reported correct inherited/partial/nested rendering in its first cycle despite registry absence. See [the select/site validation record](select-option-compatibility.md#validation); the second source-edit/reordering cycle remains required before release.

The public master tests also cover working native support, partial HTML support, broken import, exceptions, unsafe mutations, source edits and repeat import, unknown configurations/signatures and coexistence with component/form adapters. The private diagnostic is separate, nonportable integration evidence, not part of public CI.

**Known limits:** the observed native generic handler omits empty text and the literal string `"0"` during extraction. The candidate preserves that behavior. Existing native duplicate identities are not rewritten; the supplemental extractor only prevents adding another copy. Null/forwarded references, ordinary text, native string/legacy HTML origins and unknown types are not adapted. General inherited rendering is not claimed fixed.

## Native test gates

Frozen local candidate: `netmilk-wpml-component-compat-1.0.3.zip` (13,390 bytes), SHA-256 `8d40233c3e0b57da28de022fdfd940e5c244074c37cdee5e8fd2fa0222a105b2`. A checksum-addressed copy and its checksum file are preserved under ignored `dist/candidates/1.0.3/<SHA-256>/`. This is an integration candidate, not a published GitHub release.

1. Preserve the frozen 1.0.2 package and record the new candidate's exact checksum. Install it only within the separately authorized demo workflow, with the old adapter and form addon inactive and verified rollback artifacts available.
2. Capture existing translated content and raw source data **before updating**. After activation, inspect the same translations **before a new import** for unexpected changes. Confirm the effective field configuration and direct wrapper shapes on that stack.
3. Add an ordinary, non-exposed control text to a fresh synthetic master through the source editor/API. Generate fresh master jobs. If even the control fails to export, investigate post-type/job eligibility. For supported direct origins, require the heading/paragraph source texts with native identities, without adapter-added duplicates. Stop on a title-only export; never manufacture missing XLIFF units.
4. Translate only exported targets and import through WPML. Compare the source and translated raw element trees and exposed-property registry, including key/element/property associations. A registry mismatch is a remaining defect, not permission for a direct destination repair.
5. Verify a translated instance with no override against its translated master and another with an explicit override. Check actual language mapping, preview/public context, desktop/mobile output and absence of manufactured overrides, including without authentication.
6. Change the source master text and repeat a fresh export/import/render cycle. Verify exact markup, links, type/metadata preservation, stable identities and unchanged source data. Keep form-name acceptance separate from other form fields/actions, which this candidate does not add.
7. Test forwarded/nested properties separately. Translate the referenced child master through WPML and inspect native bindings/mapping; never fill a null reference with copied text. Include empty/zero controls as known native limitations, not presumed successes.

Record before/after evidence and any regression in the site's private storage. Release and migration acceptance remain pending until the applicable live gates pass.
