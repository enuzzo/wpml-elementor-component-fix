# Inherited component defaults: separate investigation

Status: diagnosis and conditional proposals only, 2026-10-02. No inherited-default fix is included in the 1.0.2 candidate. No client exports, identifying site data, private fixtures or vendor implementation are included here.

## Reported integration observations — unresolved

A separate integration session reported two completed translation cycles using **1.0.1 plus a separate form-name addon**, not the 1.0.2 candidate. The observations below are a sanitized report, not independently reproduced by this repository session.

- A deserialized MCP read exposed a title string in the source master and one translated master, but `null` in two other translated masters. This read did not expose the raw property bindings; `null` in that view cannot establish that the underlying origin value or binding is absent.
- Inherited output initially appeared empty in two target languages, including after restoring the previous adapter. After the second translation cycle and preview checks, all three target languages rendered the source default.
- The changed output does not prove a repair, correct inherited translation, or a specific root cause. Master selection, cache state and language/preview context remain hypotheses. The source default appearing in a translated page is not evidence that its master text was translated successfully.

Do not classify inherited defaults as fixed or infer a universal patch from this report. Preserve the distinction between deserialized observations and raw stored bindings, and between signed preview requests without cookies and ordinary public requests. Obtain read-only raw master/binding evidence and the effective document selected in each context before proposing an implementation. The 1.0.2 candidate had not been installed in the reporting session at the time of this update.

## What the public model establishes

An instance may reference a component without any explicit overrides. Elementor's [instance documentation](https://github.com/elementor/elementor/blob/d82b5ccd2091c21af3bb248878a494bd94989627/docs/atomic-builder/components/instances-and-overrides.md) describes that valid shape. Its [overridable transformer](https://github.com/elementor/elementor/blob/d82b5ccd2091c21af3bb248878a494bd94989627/modules/components/transformers/overridable-transformer.php) starts from the property's origin value and replaces it only when a matching override exists.

The [instance transformer](https://github.com/elementor/elementor/blob/d82b5ccd2091c21af3bb248878a494bd94989627/modules/components/transformers/component-instance-transformer.php) loads the component document, resolves its elements and renders them. The [document model](https://github.com/elementor/elementor/blob/d82b5ccd2091c21af3bb248878a494bd94989627/docs/atomic-builder/components/document-model.md) also describes the exposed-property registry in `_elementor_component_overridable_props`.

These sources are pinned to a public upstream commit, not asserted to match an installed stack. Compare actual installed versions before drawing a runtime conclusion.

An inherited value missing from the page's override export is therefore not, by itself, an extraction defect: there is no explicit page override to extract. A blank or untranslated rendered default instead requires tracing the effective master document and the property's original value. A master export containing only its title is evidence to investigate, not proof of the responsible handler.

## Original synthetic fixture design

Use this minimal invented instance in a disposable test environment:

```json
{
  "id": "synthetic-inherited-instance",
  "widgetType": "e-component",
  "settings": {
    "component_instance": {
      "$$type": "component-instance",
      "value": {
        "component_id": { "$$type": "number", "value": 1001 }
      }
    }
  }
}
```

The numeric identifiers below are invented; they must never become runtime constants in the plugin. This matrix is a fixture specification, not an executed CMS test:

| Master/relationship variant | Observation it separates |
| --- | --- |
| Source master 1001, key `synthetic-heading`, origin text `Synthetic source default` | Baseline inheritance without page overrides |
| Translated master 2001 with corresponding property and `Synthetic translated default` | Whether native language mapping selects and renders the translated master |
| Translated master with an empty origin value | Whether the empty output is already present in stored master content |
| Correct translated origin text but mismatched property/element association | Whether bindings or registry alignment prevent that content being used |
| Correct translated master but instance resolves to source or missing master | Whether the problem is relationship resolution rather than text extraction |
| Same master plus a second instance with one explicit override | Whether inherited and overridden instances remain independent |

## Read-only evidence needed from the site test

1. Record the source/target component translation relationship and the effective component document selected per language. Inspect both saved data and observed runtime selection; the stored ID alone may not reveal WPML's language mapping.
2. Compare relevant master element settings, the `overridable` wrapper, its `override_key` and `origin_value`, and the exposed-property registry's associated element/property. Record absent, empty and populated values separately.
3. Compare a fresh master-component translation job with the fresh page job: determine where the text is expected to be exported and whether it is included there. Retain native field identities.
4. Distinguish published documents from autosave/preview data and inspect public rendering without authentication. Check caches only after identifying the selected document and effective value.
5. Compare the same fixture with/without the compatibility adapter. Reproducing a failure with the previous adapter lowers confidence in a new regression but does not establish its root cause.

Private data and exports belong in the site's storage. Share only sanitized structural findings, exact versions and invented reproductions with this public project. Do not rewrite target metadata to test a hypothesis.

## Conditional fix proposals

| Confirmed failure | Candidate repair boundary |
| --- | --- |
| Native master extraction misses a string origin value | Investigate a narrowly guarded adapter around the installed WPML master-property handler, with native extraction/import probes and native identities |
| Text exports correctly but native import loses or misplaces the origin value | Reproduce the native import contract and repair only that path through WPML; preserve wrapper and registry associations |
| Translated master is correct but mapping selects the wrong document | Investigate the native WPML component relationship mechanism; changing text extraction cannot solve this |
| Stored master and mapping are correct but render is wrong | Investigate Elementor's resolution/preview/cache path; avoid adding translation fields as a rendering workaround |

No universal implementation can be justified from the symptom alone. Any future change requires a synthetic reproduction against the installed native contract, native-first fallback and a new explicit scope decision. Never materialize inherited defaults as instance overrides, detach components, add frontend text replacement or write translated data directly. Those operations would hide the failure and change inheritance semantics.
