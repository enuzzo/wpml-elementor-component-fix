# Changelog

## Unreleased

### 1.0.2 candidate

- Supplement the known flat `e-form` name registration with `form-name>value`, keeping the scalar path and native `field_id` `form-name`.
- Defer to existing nested registrations; preserve custom handlers, ambiguous identities and unknown configuration shapes. Form checks inspect registration only, unlike the component handler's native extraction/import probes.
- Add five isolated form scenarios with original synthetic field-path doubles covering extraction, import, preserved metadata/identities, idempotence, native registration, fallback and coexistence with the component adapter.
- Expand contributor instructions and add a local development/session handoff guide covering checkout verification, validation, package inspection and site boundaries.

This candidate has not been published or installed by this project session. Its live CMS export/import/render cycle remains unverified. The published 1.0.1 tag and release assets remain unchanged.

## 1.0.1 — 2026-10-02

First public release. Extracted from an internal compatibility adapter into a reusable, independently maintained project.

- Adapt explicit Elementor V4 `escaped-html` overrides using WPML's own extraction and import handler.
- Preserve native identifiers, link handling and the original Elementor value type.
- Check native plain-text and HTML extraction/import before applying the adapter.
- Leave unknown or incompatible native contracts unchanged, including a missing import method.
- Avoid the deprecated PHP 7.4 reflection type string conversion; reject nullable and unknown signatures.
- Add original synthetic regression fixtures, PHP CI, reproducible packaging, GPL licensing, and English/Italian documentation.

The internal 1.0.0 package was not a public release. These are contract-level tests; this public release has not yet completed a live CMS export/import/render verification.
