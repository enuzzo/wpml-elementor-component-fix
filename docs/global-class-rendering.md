# Global classes in translated template rendering

Status: diagnostic evidence recorded on 2026-10-03. **No global-class or kit-resolution fix is included in 1.0.4.** This record supplements the [release validation](releases/1.0.4.md); it does not replace its historical demo results.

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

Any future compatibility change needs an original synthetic reproduction, conservative handling of unknown contracts, preservation of local classes and valid translated-kit labels, and separate live acceptance covering both HTML class resolution and the CSS that actually styles those classes. Until then, this remains a documented limitation, not an implemented fallback.
