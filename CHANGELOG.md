# Changelog

## 1.0.1 — 2026-10-02

First public release. Extracted from an internal compatibility adapter into a reusable, independently maintained project.

- Adapt explicit Elementor V4 `escaped-html` overrides using WPML's own extraction and import handler.
- Preserve native identifiers, link handling and the original Elementor value type.
- Check native plain-text and HTML extraction/import before applying the adapter.
- Leave unknown or incompatible native contracts unchanged, including a missing import method.
- Add original synthetic regression fixtures, PHP CI, reproducible packaging, GPL licensing, and English/Italian documentation.

The internal 1.0.0 package was not a public release. These are contract-level tests; this public release has not yet completed a live CMS export/import/render verification.
