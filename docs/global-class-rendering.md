# Global classes in translated template rendering

Status: diagnostic evidence and an unreleased 1.0.5 candidate recorded on 2026-10-03. **No global-class or kit-resolution fix is included in 1.0.4.** This record supplements the [release validation](releases/1.0.4.md); it does not replace its historical demo results.

## Observed failure

On one recorded stack using Elementor 4.3.3, Elementor Pro 4.3.1 and WPML 4.9.7, some embedded translated templates rendered technical global class IDs instead of the labels used by the generated CSS. The resulting differences affected heading typography, button appearance and form layout even where translated text and saved style data were correct.

A temporary diagnostic observed the native class transformer before and after processing in the actual frontend request. Archived output was inspected read-only:

| Context | Observed result |
| --- | --- |
| Source-language rendering | Active kit had ten global class labels and ten ordered IDs; the transformer resolved the labels. |
| Affected English, French and German template rendering | Active kit differed from the configured source kit and had empty class labels/order; technical IDs remained unresolved. |
| Other elements in the same translated request | Rendering returned to the source kit and its populated mapping. |
| Separate global-class REST requests | Correct mappings were returned in all four languages; these did not reveal the failing embedded-template context. |
| Controlled plugin toggle | Monitored class output was identical with 1.0.4 active, inactive and reactivated. No content import occurred during the toggle. |

The site session also reported that Elementor's official Clear Files & Data operation did not remove the symptom. Cache busting or a correct REST response alone therefore cannot establish that a frontend class mapping is correct. The temporary probe was reported deactivated and removed after capture. Raw evidence, client content, class names, kit IDs and page URLs remain outside this public repository.

## What the evidence establishes

The observed rendering failure occurs when class resolution uses a kit without the expected mapping. A body class identifying the source kit is insufficient evidence: the active context can change while an embedded template renders.

Version 1.0.4 registers four filters on `wpml_elementor_widgets_to_translate`; it does not register a frontend class transformer or write kit metadata. The active/inactive comparison does not demonstrate an immediate rendering effect from this adapter. It also does **not** prove when or why translated kit metadata became incomplete, or exclude a historical effect of a native import. Those causal questions remain open.

The earlier fresh-job demo cycles established the reported translation behavior for their fixtures. HTTP success, matching form names and unchanged saved element trees do not certify global CSS resolution or every existing page's appearance.

## Acceptance and investigation procedure

1. Before updating, preserve rollback and record translations, representative screenshots and computed styles in each language at matching desktop/mobile viewports. Include reused templates, navigation buttons, headings and form layouts.
2. After activation and before any new import, compare the same pages anonymously. Preserve intended language links, anchors and page-specific template conditions.
3. After fresh WPML export and native import, repeat those comparisons. Check saved class IDs/style metadata separately from rendered class labels and the selectors in the loaded CSS.
4. If raw global IDs remain in HTML, inspect the active kit, language, preview state and ordered label map **during the affected element's rendering**. Keep any temporary instrumentation gated, observational and outside the distributed plugin, and remove it after the test. A REST request or top-level kit marker is not a substitute.
5. Before attributing a regression, separate the adapter toggle, cache operations and content imports in the evidence. Preserve earlier snapshots where available. Do not repair translated content or copy kit metadata merely to make acceptance pass.

## 1.0.5 candidate contract

The candidate implements a fallback after the native class transformer. It requires both WPML mappings (translated kit to default-language kit and back), and the source must be the configured active kit. Both posts must be published Elementor library kits. The source is instantiated directly and its ID checked; the document manager was observed redirecting an explicit source ID to the translated kit in the failing context.

Only remaining global IDs with unambiguous source labels are replaced. Target labels, ordered IDs and class post IDs each prevent replacement, even when incomplete. Native output and local classes remain unchanged. Editor/preview, admin, REST, AJAX and CLI requests are excluded. Exceptions, unfamiliar contracts and ambiguous or unsupported CSS names return native output. No metadata, language state, active-kit setting, CSS or saved Elementor data is changed.

The implementation reads ordered label maps through an explicit repository; it avoids class-post lookup methods that may clean up missing records. Relevant native contracts were inspected in the public Elementor 4.3.3 sources: [Kit construction](https://github.com/elementor/elementor/blob/4.3.3/core/kits/documents/kit.php), [document construction](https://github.com/elementor/elementor/blob/4.3.3/core/base/document.php) and [global-class repository](https://github.com/elementor/elementor/blob/4.3.3/modules/global-classes/global-classes-repository.php). Third-party native hooks and future dependency behavior are outside the synthetic harness.

The site session reported that global-class metadata fields were configured as ignored by WPML, while translated kits lacked those fields. Merely updating the kit translation job is therefore not a demonstrated alternative on that stack. No translation preferences were changed for this candidate. This is not a claim that all WPML installations share that configuration.

### Evidence and immutable package

- 63 synthetic scenarios pass locally, including 18 new global-class scenarios; all 14 PHP files pass lint on PHP 8.5.11. Python 3.14.7 verifies the exact three-file installable ZIP. These are not CMS tests.
- A separate temporary query-only proof used an explicit source Kit and resolved the monitored IDs in five anonymous page requests across four languages. Archived after-transform output was inspected read-only. That proof had fewer guards than the candidate and did not certify its installation or computed styles. Temporary instrumentation was reported removed.
- The four translation filters are unchanged from 1.0.4. New translation regression cycles and native-support/removal checks remain necessary before release.

Frozen candidate: `dist/candidates/1.0.5/f29eddb6b429a3638c3e75e7e6731bea1abcd8294f4fd248d9bfa5821b3f4844/netmilk-wpml-component-compat-1.0.5.zip`, **17,923 bytes**. A separate `SHA256SUMS` accompanies it. No tag or public release has been created.

```text
f29eddb6b429a3638c3e75e7e6731bea1abcd8294f4fd248d9bfa5821b3f4844  netmilk-wpml-component-compat-1.0.5.zip
```

### Live gates before release

1. Record backup/rollback, installed versions and the candidate checksum. Capture translated texts, class output, screenshots and computed styles before installation; install only under the site's separate authorization.
2. Compare the same source/translated pages after activation and before imports, anonymously at desktop/mobile widths. Cover embedded templates, headings, buttons, forms and intentional navigation variants. Inspect loaded CSS selectors: the kit identity guard alone cannot prove that the matching stylesheet was generated or delivered.
3. Verify native resolved classes, local classes, target-owned classes, editor/preview behavior and the absence of persistent kit/element changes. If cached markup prevents execution of the transformer, handle cache operations separately and record them; the plugin does not purge caches.
4. Run fresh WPML export/import/render regressions for the existing text, master, form-name and select behaviors; compare sources and saved targets and repeat after a source change. Correct styling alone is not translation acceptance.
5. Report the exact tested bytes, observed differences and unresolved cases before promoting the candidate. If code or packaged documentation changes, build a distinct frozen candidate and repeat the affected gates. Never replace a tested ZIP in place.
