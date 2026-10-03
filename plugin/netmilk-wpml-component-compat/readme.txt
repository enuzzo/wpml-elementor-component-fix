=== Netmilk — WPML Elementor Component Fix ===
Contributors: enuzzo
Tags: wpml, elementor, translation, components, xliff
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A temporary WPML adapter for Elementor V4 text and translated-kit global class labels.

== Description ==
Adapts escaped-html overrides through the installed WPML native handler. Preserves identifiers, link handling and stored value types. Checks native extraction and import with plain text and HTML; defers when both work. Unknown native contracts are left unchanged.

Also supplements the known e-form form-name registration with form-name>value, preserving field_id form-name and the scalar path. Existing nested registrations and unknown/custom configurations are left unchanged. This is a registration check, not a native form import probe.

Version 1.0.3 also delegates direct exposed heading/paragraph escaped-html origins to the native node handler using an in-memory string copy, restoring the original type on import. Native plain-text/HTML round-trip probes bypass this adaptation when support works. Original field paths and identities remain native. Existing origin paths and unknown/custom configurations are left unchanged. It does not create instance overrides, resolve null/forwarded properties or synchronize the master registry. Native master-text omissions of empty text and the literal "0" remain unchanged.

Version 1.0.4 also delegates typed select-option labels to the native item handler, using an in-memory collection alias and stable surrogate item IDs. Technical option values stay unchanged. Duplicate labels stay distinct after reordering; the visible label "0" is included and blank labels remain omitted. Native extraction/import probes bypass normalization when support works; unknown contracts and ambiguous values are left unadapted. No temporary IDs or aliases are stored.

Version 1.0.5 adds a frontend fallback for unresolved global class IDs when the active translated kit maps through WPML to the configured source kit. It reads ordered labels from an isolated source Kit instance. Target class declarations, local classes and native resolved names take precedence. Editor/preview, admin, REST, AJAX and CLI are excluded; unknown contracts and ambiguous labels remain unchanged. It does not copy kit metadata, alter CSS, switch language or write saved Elementor data.

No settings, database tables, independent translated-page writes, frontend assets, network calls or telemetry. General inherited-default resolution and form fields/actions beyond names and select-option labels remain unsupported. Fresh WPML jobs and a real export/import/render check, including master registry consistency and computed styles, are required. Version 1.0.5 is a development candidate pending live-stack verification and publication. The latest public release remains 1.0.4.

Documentation and source: https://github.com/enuzzo/wpml-elementor-component-fix
Independent community project, not affiliated with WPML or Elementor. No WordPress.org directory listing is claimed.

== Installation ==
1. For this development candidate, use the separately supplied frozen ZIP and verify its SHA-256. GitHub Releases contains the latest public version, 1.0.4.
2. Upload through Plugins > Add New > Upload Plugin and activate.
3. Test explicit overrides on a draft page using fresh WPML translation jobs.
4. Verify the XLIFF source fields, import translated targets through WPML and inspect the public translated page.

== Removal ==
After updating WPML/Elementor, deactivate this plugin on a test site and verify a fresh export/import/render cycle. If the native cycle works for the fields you use, delete the plugin. It owns no stored data. Previously missing fields can return in future jobs if native support remains broken.

== Changelog ==
= 1.0.5 (unreleased candidate) =
Add guarded frontend global-class label recovery for translated kits, with bidirectional WPML mapping and explicit source identity. Preserve native resolution and all target class declarations; no persistent writes. Add 18 synthetic scenarios (63 total). The query-only mechanism was demonstrated separately; this packaged candidate still requires live acceptance, including CSS and computed styles. The four translation filters are unchanged from 1.0.4.

= 1.0.4 =
Delegate typed select-option labels through native WPML extraction/import. Preserve technical values and stable identities for duplicate labels/reordering, include zero labels locally, and reject ambiguous contracts. Two fresh-job live cycles passed on one stack. Live XLIFF omitted zero labels, which remained preserved in data/rendering. This release has no translated-kit class fallback; see repository documentation for post-release findings.

= 1.0.3 (unreleased) =
Adapt direct exposed escaped-html heading/paragraph origins through the native node handler with runtime probes and original identities. Add synthetic regression tests. Direct master-text export/import was subsequently verified on one site stack; registry consistency and full rendering acceptance remain unresolved. Frozen 1.0.2 artifacts remain unchanged.

= 1.0.2 (unreleased) =
Add guarded nested e-form name registration with preserved field identity and synthetic regression tests. No completed live CMS cycle is claimed.

= 1.0.1 =
First public release with conservative native-contract guards, synthetic tests and reproducible packaging.
