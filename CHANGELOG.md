# Changelog

## [Unreleased] — 1.0.3 candidate

### Fixed

- Adapt direct exposed `e-heading.title` and `e-paragraph.paragraph` origins stored as `escaped-html` through the installed native node handler. Use a temporary `string` copy and restore the original type in the returned update, preserving native fields, identities and metadata.
- Run native plain-text/HTML extraction/import probes in the actual element context, after native field processing; bypass adaptation when they pass. Preserve native active-field rules and avoid seeding its cache with a fabricated widget during registration.
- Reject unexpected delegated mutations and unknown contracts; add no competing field paths, instance overrides, registry writes or component remapping.

### Added

- Add 12 isolated master-origin scenarios, bringing the public suite to 32. Cover native support and partial failures, HTML, identity deduplication, source immutability, repeated import/source edits, cache/active fields and conservative fallback.
- Document the master type mismatch, inherited-default boundaries and before/after translation acceptance gates.

### Validation and limits

- Local synthetic checks pass. A separate local diagnostic passes 28 checks using the installed native node-handler source with environment stubs. Vendor sources and private evidence remain outside the public repository; this diagnostic does not load WordPress or certify a live cycle.
- A separate site session reported passing 1.0.3 master extraction and native import with the frozen candidate alone. Archived XLIFF/WXR comparison confirmed both direct origins translated in English/French/German, preserving the remaining element tree. The translated masters lack their dedicated property registry; full acceptance remains open.
- Record missing select-option labels in a subsequent extended page export and reported rollback without importing those incomplete page jobs. The public select registration matches the stored shape, but an isolated installed-handler diagnostic reproduces nested-collection lookup failure, colliding identities without item IDs and omission of the visible zero label. Effective site config still needs verification. See [the metadata and select diagnosis](docs/integration-gaps.md). No runtime or frozen-package change accompanies these findings.
- The native wrapper resolver supports string and legacy HTML origins but omits escaped-html. Fresh master exports under 1.0.2 and the earlier adapter both reportedly contained only the document title, supporting a pre-existing gap rather than a 1.0.2 regression.
- General inheritance, null/forwarded bindings, master-language mapping and property-registry synchronization remain outside this narrow fix. The native omission of empty strings and literal `"0"` in master extraction is preserved and is not claimed fixed.
- Component override and form-name filters are unchanged from 1.0.2. No completed 1.0.3 site test or definitive release is claimed. The published 1.0.1 release and frozen 1.0.2 package remain unchanged.

## 1.0.2 candidate — unreleased history

### Fixed

- Supplement the known flat `e-form` name registration with `form-name>value`, keeping the scalar path and native `field_id` `form-name`.
- Defer to existing nested registrations; preserve custom handlers, ambiguous identities and unknown configuration shapes. Form checks inspect registration only, unlike the component handler's native extraction/import probes.

### Added

- Add five isolated form scenarios with original synthetic field-path doubles covering extraction, import, preserved metadata/identities, idempotence, native registration, fallback and coexistence with the component adapter.
- Add development/session handoff and form-name diagnosis guides, including a before/after translation acceptance procedure and rollback expectations.
- Add a dedicated demo protocol with a frozen candidate digest and read-only checks for raw bindings, master-language mapping and rendering context; test authorization does not establish acceptance.
- Document a separate inherited-default investigation with synthetic fixture designs and conditional proposals; no runtime support for inherited values is added.

### Changed

- Set candidate package metadata to 1.0.2 and expand contributor instructions and English/Italian documentation. The component override filter is unchanged from 1.0.1.

### Validation status

- Local PHP syntax checks, all 20 synthetic scenarios and package integrity/checksum checks passed. Consult GitHub Actions for the candidate's branch/commit-specific CI result.
- Real-stack acceptance remains pending: compare existing translations before/after updating, then test fresh XLIFF export, native import and rendered results, including a second cycle after a source edit.
- Record two reported cycles for 1.0.1 plus a separate form-name addon, explicitly outside candidate acceptance. Inherited output varied between observations; neither its cause nor a fix is established, and deserialized MCP reads do not expose raw bindings.
- Record the final reported rollback to the previous site configuration: migration remains incomplete because inherited-default acceptance is unresolved. At that point 1.0.2 had not been installed; its subsequent isolated master export test is recorded above and did not complete an import/render cycle.

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
