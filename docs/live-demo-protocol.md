# Isolated 1.0.2 demo: artifact and read-only diagnosis

Status, 2026-10-02: a dedicated demo test of 1.0.2 without the separate form-name addon is planned. No candidate live result is established. Earlier combined-setup results and rollback are historical observations, not acceptance of this candidate.

## Frozen candidate

- ZIP: `netmilk-wpml-component-compat-1.0.2.zip`, 11,110 bytes.
- SHA-256: `9491879a9b14b00b088ec59db1e79d772cac524b7a2fad7d39fd8f5b1c031c41`.
- A checksum-addressed local copy is preserved under `dist/candidates/1.0.2/<SHA-256>/`, with its own `SHA256SUMS`. This ignored artifact directory is not part of the installable payload.
- Recheck the digest before installation and record the actual active plugin/version. Future runtime/package corrections must use a new candidate version and digest; do not silently replace this test artifact. Documentation-only commits do not change its bytes.

## Scope and sequence

Use a dedicated synthetic source page and a synthetic source master. Translate both through fresh native WPML jobs, without editing translated documents directly. Keep production masters outside the experiment. Capture the source and existing translated state before each update/import. Prove that the form-name addon and previous full adapter are inactive during candidate testing.

Cover distinct/repeated overrides, markup/entities, links, empty string and string `0`, multiple form names, and a second source-update cycle. Distinguish explicit empty overrides from absent overrides in persisted data: the editor or native export may normalize/omit empty strings. Record that behavior rather than manufacturing a target segment or changing an inherited value into an override.

For inheritance, start with one master, one exposed text property, one instance with no overrides and another with an explicit override. Give the translated master an unmistakable synthetic target text through WPML. Complete and inspect the master job separately from the page job. An inherited default need not appear in the page's override export; check the master export before classifying it as missing.

## Read the original binding, not an MCP display value

Obtain the persisted `_elementor_data` and `_elementor_component_overridable_props` through an already authorized read-only route that preserves nested JSON. Retain exact raw values and presence flags privately before decoding. A missing field in REST or MCP may mean it is not exposed; it does not prove absent metadata. If using WordPress metadata APIs, retain `metadata_exists` alongside the returned value and note any installed read filters.

In each relevant master element, inspect the complete `settings` property. A minimal invented binding could look like:

```json
{
  "$$type": "overridable",
  "value": {
    "override_key": "synthetic-heading",
    "origin_value": {
      "$$type": "escaped-html",
      "value": "Synthetic master default"
    }
  }
}
```

Find wrappers recursively, without assuming the property is named `title` or that every origin uses `escaped-html`. Record the element ID, property path, wrapper type, `override_key`, complete typed `origin_value`, and matching registry entry. Compare bindings within each master first, then compare corresponding source/target properties; do not assume element IDs must be identical across translations. Do not confuse the WordPress document title with a heading property's origin value.

Elementor [4.3.3's component document](https://github.com/elementor/elementor/blob/4.3.3/modules/components/documents/component.php) reads the exposed-property registry and contains an alignment method that **writes** metadata. Inspect its model, but do not call `align_overridable_props_with_elements()`, save routines or migration helpers as a diagnostic: that would change the evidence.

## Separate language mapping from effective rendering

Use the documented WPML read filters in the site's existing diagnostic context. The following is a read-only fragment, not plugin code; supply the synthetic source component ID and target language from that context:

```php
$trid = apply_filters( 'wpml_element_trid', null, $source_component_id, 'post_elementor_component' );
$translations = $trid
    ? apply_filters( 'wpml_get_element_translations', null, $trid, 'post_elementor_component' )
    : [];
$mapped_id = apply_filters( 'wpml_object_id', $source_component_id, 'elementor_component', false, $target_language_code );
```

The `post_` prefix belongs to the group-information hooks, while `wpml_object_id` takes the CPT key. Passing `false` avoids hiding a missing mapping behind the original ID. First verify WPML is active and these filters are registered; an unhandled filter may simply return its input. Cross-check the returned group, target language, document ID/type and status. See [`wpml_element_trid`](https://wpml.org/wpml-hook/wpml_element_trid/), [`wpml_get_element_translations`](https://wpml.org/wpml-hook/wpml_get_element_translations/) and [`wpml_object_id`](https://wpml.org/wpml-hook/wpml_object_id/).

Record three IDs separately: the instance's persisted `component_id.value`, WPML's mapped master ID, and the actual document loaded for rendering. Mapping availability is not proof that Elementor used it. If authorized debugging can expose the loaded document, record it; otherwise mark runtime selection as unknown rather than inferring it from matching text. An expected ID mismatch is a lead, not a license to rewrite translated references.

Elementor [4.3.3's instance transformer](https://github.com/elementor/elementor/blob/4.3.3/modules/components/transformers/component-instance-transformer.php) selects preview/editor autosave behavior before rendering. Record request language, preview/public mode, authentication/cookies and loaded document/autosave identity. A signed preview without cookies is still a preview. Compare published public output only when authorized for that demo; do not call it verified if only previews were available.

The [overridable transformer](https://github.com/elementor/elementor/blob/4.3.3/modules/components/transformers/overridable-transformer.php) starts from the origin and substitutes a matching override. With none present, test the selected master's origin rather than creating an instance override to force an expected result. Confirm installed source matches the relevant version before applying these observations to a live stack.

## Return a minimal evidence matrix

For each synthetic property/language/cycle, report: package digest; job type (master/page); raw presence/type of override and origin; registry association; mapping result; loaded document if observable; XLIFF field identity and source presence; native import result; persisted target leaf; and rendered output/context. Keep private IDs, exports and signed URLs in the site's workspace, and share only sanitized structural evidence here.

Classify a failure at the first verified break: master extraction, native import, relationship mapping, binding resolution or rendering context. If raw evidence is unavailable, retain that uncertainty. No inherited-default fix or successful 1.0.2 cycle is claimed by this protocol.
