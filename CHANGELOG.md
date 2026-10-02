# Changelog

## [Unreleased] — 1.0.2 candidate

### Fixed

- Supplement the known flat `e-form` name registration with `form-name>value`, keeping the scalar path and native `field_id` `form-name`.
- Defer to existing nested registrations; preserve custom handlers, ambiguous identities and unknown configuration shapes. Form checks inspect registration only, unlike the component handler's native extraction/import probes.

### Added

- Add five isolated form scenarios with original synthetic field-path doubles covering extraction, import, preserved metadata/identities, idempotence, native registration, fallback and coexistence with the component adapter.
- Add development/session handoff and form-name diagnosis guides, including a before/after translation acceptance procedure and rollback expectations.

### Changed

- Set candidate package metadata to 1.0.2 and expand contributor instructions and English/Italian documentation. The component override filter is unchanged from 1.0.1.

### Validation status

- Local PHP syntax checks, all 20 synthetic scenarios and package integrity/checksum checks passed. Consult GitHub Actions for the candidate's branch/commit-specific CI result.
- Real-stack acceptance remains pending: compare existing translations before/after updating, then test fresh XLIFF export, native import and rendered results, including a second cycle after a source edit.

No 1.0.2 release has been published or installed by this project session. Its live CMS export/import/render cycle remains unverified. The published 1.0.1 tag and release assets remain unchanged.

## [1.0.1] — 2026-10-02

First public release. Extracted from an internal compatibility adapter into a reusable, independently maintained project.

- Adapt explicit Elementor V4 `escaped-html` overrides using WPML's own extraction and import handler.
- Preserve native identifiers, link handling and the original Elementor value type.
- Check native plain-text and HTML extraction/import before applying the adapter.
- Leave unknown or incompatible native contracts unchanged, including a missing import method.
- Avoid the deprecated PHP 7.4 reflection type string conversion; reject nullable and unknown signatures.
- Add original synthetic regression fixtures, PHP CI, reproducible packaging, GPL licensing, and English/Italian documentation.

The internal 1.0.0 package was not a public release. These are contract-level tests; this public release has not yet completed a live CMS export/import/render verification.

[Unreleased]: https://github.com/enuzzo/wpml-elementor-component-fix/compare/v1.0.1...codex/v4-form-name-compat
[1.0.1]: https://github.com/enuzzo/wpml-elementor-component-fix/releases/tag/v1.0.1
