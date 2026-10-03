# Contributor instructions

## Project purpose and scope

Netmilk — WPML Elementor Component Fix is a standalone, public WordPress plugin: a temporary, reusable adapter for explicit Elementor V4 component text overrides stored as `escaped-html` that the native WPML handler omits. It also addresses the narrow `e-form` name registration mismatch (`form-name>value` with native identity `form-name`). Version 1.0.3 adapts the escaped-html type in memory for direct exposed `e-heading.title` and `e-paragraph.paragraph` master origins. Version 1.0.4 also delegates typed select-option labels through WPML’s native item handler. Version 1.0.5 adds a guarded frontend label fallback for global classes in verified translated kits. It does not translate independently. General inherited-default resolution, null/forwarded bindings, other master properties and other form fields/actions remain unsupported. See [architecture](docs/architecture.md) for the implementation.

## Repository map

- `plugin/netmilk-wpml-component-compat/netmilk-wpml-component-compat.php`: plugin entrypoint and adapter; adjacent `readme.txt`: WordPress package metadata.
- `tests/`: isolated contract scenarios and original synthetic doubles.
- `scripts/build.py`: allowlisted ZIP builder; generated `dist/` is ignored by Git.
- `README.md`, `docs/`, `CONTRIBUTING.md`, `CHANGELOG.md`: public guides, operating procedure and history; `LICENSE`: GPL-2.0-or-later.
- `assets/`: repository artwork; `.github/workflows/`: CI; `.github/ISSUE_TEMPLATE/`: compatibility reports.

## Implementation invariants

Delegate extraction, field identifiers, link handling and import to WPML. Adapt only an in-memory copy, leave source data immutable, and restore the original `escaped-html` type in the returned update. Add no persistent data or independent destination synchronization.

Preserve the native plain-text and HTML extraction/import probes. Retain the official handler when they pass; leave unknown or incompatible contracts and other handlers unchanged. Keep production code compatible with the declared PHP minimum (currently 7.4); do not infer compatibility from a newer local interpreter alone.

For form names, preserve the scalar registration and its metadata and use native `field_id` for the additional nested path. Existing nested registrations, custom integrations and unknown/ambiguous configuration contracts must remain unchanged. Do not claim a registration check is a native runtime round-trip probe.

For direct master text origins, compose the native node handler with its original heading/paragraph fields. Add no competing text paths. Run native plain-text/HTML probes from the integration callback in the actual element context, never on a fabricated widget during registration (native active-settings caches must remain valid). Preserve native identities, metadata and the stored escaped-html type. Existing origin paths/custom handlers take precedence. Do not fabricate overrides, resolve null/forwarded origins, write the property registry or remap components. Native falsey-string omissions remain unchanged; registry consistency and inherited rendering require separate live acceptance.

For select options, require the known native item registration and unique nonempty technical values. Keep the collection alias, surrogate item IDs and zero-label placeholder in memory only. Delegate string naming and import to WPML; never translate technical option values or use labels/positions as identity. Reject collisions and unexpected mutations. Leave other configurations unchanged and bypass normalization when native probes pass. Frozen candidates must not be overwritten.

Version 1.0.5 adds a frontend global-class label fallback. Require bidirectional WPML kit mapping to the configured source kit; read it through an isolated, identity-checked Kit instance because the document manager can redirect source reads. Preserve all target declarations (labels, order and class post IDs), local classes and native resolved output. Exclude editor/preview, admin, REST, AJAX and CLI. Never change kit/language state, metadata, CSS or saved Elementor data. Unknown contracts and ambiguous labels are no-ops. Validate actual loaded CSS and computed styles separately before release.

## Translation and site boundaries

Translate through WPML only. Never write translated Elementor data directly or bypass WPML. Real-stack checks use draft source pages, fresh translation jobs, XLIFF export, native WPML import and rendered-page inspection. Read the specific site's project instructions before site work; repository work does not authorize installation, publication or client-site changes.

## Public repository hygiene

Never add credentials, client exports, backups, personal data, site-specific IDs or URLs, or proprietary WPML/Elementor code. Fixtures and test doubles must be original and synthetic. Keep private integration evidence in the relevant site's approved storage, outside this public repository.

## Validation

Run `php tests/run.php` and `python3 scripts/build.py`, lint all PHP files under `plugin/` and `tests/`, and inspect the CI workflow and applicable run results. Verify the ZIP's exact payload and checksum. The [development guide](docs/development.md) contains the commands.

Always distinguish synthetic contract tests, plugin installation/activation, and a verified live export/import/render cycle. Report runtime versions and untested layers explicitly. Do not treat local checks as a completed remote CI run.

## Releases and removal

Keep plugin version, WordPress stable tag, changelog and versioned documentation consistent. Documentation-only setup does not require a plugin version bump or new release; record pending changes under Unreleased. Release work is a separate task.

Release ZIPs must contain one installable plugin folder with only the allowlisted entrypoint, readme and license, plus a separate SHA-256 checksum file. Exclude repository tooling. After native support is verified on the actual stack with the adapter deactivated and fresh jobs, remove it through WordPress Plugins; it owns no persistent data. Passing probes bypass the adapter, not deactivate or uninstall it.

## Working style

Communicate with the maintainer in Italian; keep primary public documentation in English. Explain decisions and limitations concisely, preserve pre-existing changes, and use `codex/...` branches for new changes. Inspect checkout, origin, branch and current release before starting; preserve the repository layout. Use the [development guide](docs/development.md) to resume work without duplicating these rules in other guides.
