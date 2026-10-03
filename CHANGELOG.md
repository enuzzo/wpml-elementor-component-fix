# Changelog

## [Unreleased]

No pending changes recorded.

## [1.0.5] — 2026-10-03

### Fixed

- Add a frontend-only fallback after Elementor's native class transformer. Resolve only remaining global IDs from the configured source kit's ordered labels, with bidirectional WPML kit mapping, published-kit checks and an isolated source Kit whose identity is verified. The normal document getter may redirect a source ID to its translated kit.
- Preserve native resolved names, local classes and target declarations found in labels, order or class post IDs, including incomplete declarations. Reject ambiguous/colliding labels and unknown contracts. Exclude editor/preview, admin, REST, AJAX and CLI; perform no persistent writes, language switching or CSS regeneration.
- Keep all four 1.0.4 translation filters unchanged.

### Validation and limits

- Add 18 original global-class scenarios and the post-1.0.4 legacy-master boundary scenario: 63 isolated public scenarios in total. Lint, tests and packaging pass on the PHP 7.4/8.1/8.3/8.5 CI matrix.
- The exact frozen package passed the recorded live class-resolution acceptance on one stack: 24 anonymous pages with zero unresolved global IDs, compared with 126 occurrences across 18 pages before installation. Twelve pages were checked at desktop 1280 px and mobile 375 px, including navigation variants and form/upload styling.
- Six fresh WPML jobs for navigation and form templates completed native import in English, French and German. All 84 existing translated segments and the six XLIFF files were preserved byte for byte; the final offline guard reported zero errors and zero unreviewed warnings. Rendering remained correct after native import and a separately recorded hosting-cache purge.
- REST-visible metadata hashes were identical for all 40 compared objects; only the modification timestamps of the six imported templates changed. This is not a complete database audit. No real form submission or new source-edit/reordering cycle was performed in this acceptance. Earlier source-edit coverage belongs to the separate 1.0.4 evidence. See the [1.0.5 validation matrix](docs/releases/1.0.5.md).
- Publish the exact 17,923-byte tested ZIP, SHA-256 `f29eddb6b429a3638c3e75e7e6731bea1abcd8294f4fd248d9bfa5821b3f4844`. The packaged readme retains candidate wording to preserve the verified bytes; repository documentation and release notes record acceptance. The official 1.0.4 artifact remains unchanged.

### Documentation

- Record the global-class rendering diagnosis and require before/after class, CSS and computed-style checks. The controlled 1.0.4 toggle did not demonstrate an immediate adapter regression; the historical origin of incomplete translated-kit metadata remains unverified.
- Include the previously documented [legacy master recovery](docs/legacy-master-recovery.md): a fresh native master job recovered resolved numeric headings despite omission of the numeric unit from XLIFF. The synthetic boundary test does not imply automatic migration of old targets.

## [1.0.4] — 2026-10-02

First public update after 1.0.1; includes the form-name and direct master-origin fixes developed in the unpublished 1.0.2/1.0.3 candidates below.

### Included from earlier candidates

- Expose wrapped form names through `form-name>value` with the native `form-name` identity, preserving existing scalar/custom registrations.
- Delegate direct exposed heading/paragraph `escaped-html` origins through WPML with temporary type conversion, native runtime probes and restored stored types. No fabricated overrides or registry writes.

### Fixed

- Delegate the known V4 select-option label registration to the native item handler using an in-memory collection alias and surrogate item IDs derived from unique nonempty technical values. WPML still generates string identities and applies imports; technical values and saved Elementor data structures remain unchanged.
- Return the visible label `"0"` from the delegated item extractor, preserve distinct identities for duplicate labels across reordering/source edits, and keep blank labels omitted. Reject ambiguous values, colliding names and unexpected native mutations. No temporary IDs or aliases are persisted.
- Probe native plain/HTML/zero extraction and item import before normalization; defer to working native support and retain conservative configuration/API guards.

### Validation and limits

- Add 12 original select scenarios: 44 isolated public scenarios in total. A separate local diagnostic passes 20 checks on the installed native item handler and outer import dispatcher with environment stubs. These checks do not establish a live site cycle.
- Keep the 1.0.3 component, master-origin and form-name filters unchanged. Preserve the frozen 1.0.2/1.0.3 packages.
- Two live cycles passed in English, French and German on one recorded stack, using fresh XLIFF jobs and native import. The second cycle verifies source edits, markup/links, form names and reordered select labels with stable identities. Saved target data retains technical values and contains no temporary IDs/aliases; recursive comparison of the full element trees finds only reviewed translations, with no unexpected structural/technical differences. See the [validation matrix](docs/releases/1.0.4.md).
- Both demo export cycles contain 64 units per language, including three human-readable select labels with distinct identities. The zero option is absent from live XLIFF but preserved in data/rendering. Elementor removes the submitted blank option before WPML; four rows persist. Persisted blank-row live handling is not certified.
- Final checks on eight existing public pages found HTTP 200 and form names matching the baseline. An observed layout overflow was also reproduced with the previous adapter; it is not a demonstrated plugin regression.
- Component-registry synchronization remains unimplemented. Registry absence caused no frontend defect in the tested fixture; this does not certify every editor/component workflow or general inherited-default resolution.
- Publish the exact 16,080-byte frozen ZIP tested live, SHA-256 `95fc63ddb72778105a677fbf1fb2e56dab5460af591bb136b51d69c34602b50f`. Its packaged readme retains pre-acceptance candidate wording to preserve the verified bytes; the release notes and repository documentation record final status. Earlier candidates were not separate public releases.

## 1.0.3 candidate — unreleased history

Status below records that candidate, before the accepted 1.0.4 release above.

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

Status below records that candidate, before the accepted 1.0.4 release above.

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

[Unreleased]: https://github.com/enuzzo/wpml-elementor-component-fix/compare/v1.0.4...HEAD
[1.0.4]: https://github.com/enuzzo/wpml-elementor-component-fix/compare/v1.0.1...v1.0.4
[1.0.1]: https://github.com/enuzzo/wpml-elementor-component-fix/releases/tag/v1.0.1
