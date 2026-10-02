# Integration gaps after the 1.0.3 master cycle

This records the 1.0.3 diagnosis and evidence handoff. The frozen 1.0.3 ZIP remains unchanged. The subsequent [1.0.4 release](releases/1.0.4.md) addresses select labels and passed two live cycles plus saved-data/production-page checks. Component-registry synchronization remains unimplemented; the tested rendering does not justify adding it.

## Master metadata: a separate native synchronization gap

The site session reported successful native import of the three fresh master jobs. Local read-only comparison of the archived XLIFF targets with the subsequent native WXR export confirmed that each translated element tree equals the source tree with only the two direct origin texts changed. Types, override keys, element IDs and other element data remain intact.

The same evidence shows:

- The dedicated `_elementor_component_overridable_props` registry and `_elementor_component_uid` are absent from all three translated masters.
- `_elementor_page_settings` exists and equals the source settings, including the source-language origin values in `overridable_props`.
- The native property-registry API reports null for the translated masters. This is consistent with the missing dedicated meta; copied page settings are not that registry.

The current [public WPML Elementor configuration](https://cdn.wpml.org/wpml-config/elementor/wpml-config.xml), inspected on 2026-10-02, marks `_elementor_page_settings` as `copy-once` and does not declare the component-specific registry or UID keys. That supports a missing metadata-policy hypothesis. It does **not** establish the site's effective configuration: installed defaults, cached remote config and site overrides still need inspection.

Elementor 4.3.3's [component document](https://github.com/elementor/elementor/blob/4.3.3/modules/components/documents/component.php) reads the registry from the dedicated key. Its [component module](https://github.com/elementor/elementor/blob/4.3.3/modules/components/module.php) persists the registry from component settings during the document save flow. Writing translated `_elementor_data` and copying page settings does not by itself demonstrate that this save flow ran.

The document's alignment method is a **writer**, not a diagnostic, and iterates the existing registry rather than reconstructing missing property definitions. The [native API](https://github.com/elementor/elementor/blob/4.3.3/modules/components/components-rest-api.php) only invokes its compatibility alignment for older document versions. Do not use a target editor save, alignment call or direct meta repair to make this acceptance gate appear green.

The first subsequent 1.0.4 cycle reportedly rendered explicit/inherited/partial/nested cases correctly despite registry absence. That fixture does not justify adding metadata synchronization; it also does not certify every editor/component workflow.

### Consequences and repair boundary

A missing registry does not alone prove inherited frontend text will be blank. The [overridable transformer](https://github.com/elementor/elementor/blob/4.3.3/modules/components/transformers/overridable-transformer.php) can use the translated element-tree origin. Conversely, the [override parser](https://github.com/elementor/elementor/blob/4.3.3/modules/components/prop-types/component-override-parser.php) uses the registry selected by `schema_source.id` and can discard unmatched overrides during sanitization. Compare actual component/schema references and render context; do not infer output from metadata presence alone.

A blanket `copy` rule is insufficient for a complete repair: the source registry contains origin text, which must remain consistent with WPML's translated element tree. A generic solution must establish both native copying of structural metadata and correct ordering/derivation of translated origins. WPML documents [custom-field copy policies](https://wpml.org/documentation/support/language-configuration-files/custom-fields-translation-options/) and a [copied-value filter](https://wpml.org/wpml-hook/wpml_sync_custom_field_copied_value/), but those APIs alone do not prove that the target's freshly translated data is available when metadata is copied. No implementation is selected before inspecting the installed flow. UID, variants and archive fields require their own semantics; do not copy every component meta indiscriminately.

## Select option labels: native registration already exists publicly

The extended demo session reported missing select-option labels in all three fresh page exports and stopped before importing those incomplete jobs. It reported rollback to the previous adapter and cancellation of the unused jobs. Other exported form texts do not establish select support.

Read-only inspection of the archived source confirms this shape (invented values below):

```json
{
  "options": {
    "$$type": "options",
    "value": [{
      "$$type": "key-value",
      "value": {
        "key": {"$$type": "string", "value": "Visible option"},
        "value": {"$$type": "string", "value": "stable-option-id"}
      }
    }]
  }
}
```

The public WPML configuration already declares `e-form-select` item collection `options>value` and label field `value>key>value`. Those paths reach the visible labels in the observed shape. The technical option value is a different leaf and must remain unchanged.

The installed item handler was subsequently supplied privately and inspected. A separate original diagnostic loaded those installed classes with environment stubs and passed eight checks demonstrating two failures when supplied the public rule:

- The collection lookup uses a literal settings key, so the nested `options>value` collection is not reached. Under strict diagnostics the missing key raises an error; this is not evidence of the site's error-reporting policy.
- If only that collection is exposed through a temporary literal alias, labels become readable but share the same native identity because the typed option rows lack `_id`. A test translating the second row then updates the first row. This proves that fixing lookup alone is unsafe.

The same diagnostic confirms native omission of the visible label `"0"` and the empty label. The site's attempted source input includes two identical visible labels with distinct technical values, a zero label and an empty label. A fresh source read later confirmed that Elementor drops the empty-label row during normal saving, before WPML, leaving four persisted rows. The subsequent 1.0.4 live cycle reportedly preserves zero and translates the three human labels; it does not exercise a persisted blank row. Future tests must distinguish those cases, preserve technical values and maintain distinct, stable identities after reordering. Visible labels and numeric positions are insufficient as identity sources.

These isolated findings do not prove which configuration the live job actually loaded. Compare the effective registration with the public rule before selecting a repair. Another registration could duplicate future native support; a compatible adapter would need native extraction/import delegation, safe item identity and insertion-path behavior, plus conservative fallback for ambiguous option values. Select labels remain outside the frozen 1.0.3 runtime. The subsequent [1.0.4 candidate](select-option-compatibility.md) implements guarded native delegation for the known rule, subsequently accepted through two live export/import/render cycles on one recorded stack. See the [1.0.4 validation record](releases/1.0.4.md); other stacks still require their own acceptance.

## Remaining read-only integration evidence

Keep installed vendor files and raw site evidence in the site's private storage, outside this repository:

1. **Received and inspected:** installed `ModuleWithItemsFromConfig.php` (`WPML\PB\Elementor\Modules\ModuleWithItemsFromConfig`) and its `WPML_Elementor_Module_With_Items` base. The eight-check private diagnostic above covers the supplied source with simulated dependencies.
2. The site's effective `e-form-select` configuration, including fields, item fields and integration classes. Where runtime inspection is unavailable, start with the installed Elementor XML/config cache and any explicit site override, without changing them.
3. The installed `WPML_PB_Elementor_Handle_Custom_Fields_Factory` and the handler it creates, plus `WPML_Elementor_Update_Translation` and the relevant metadata-copy/save service. These establish copy exclusions and ordering relative to translated element data.
4. The effective policies for the component registry/UID, page settings and element data, with the installed constants' meanings. Inspect only relevant entries; do not export complete settings, credentials or unrelated client data.
5. Read-only component/schema reference checks and the already authorized render observations. No additional target save or repair is necessary for diagnosis.

Any later correction must preserve native IDs, technical option values, translated origins and valid null inheritance, then pass fresh export/import/render tests and a second cycle after a source edit. The metadata and select gaps are separate from the demonstrated direct-origin extraction/import fix.
