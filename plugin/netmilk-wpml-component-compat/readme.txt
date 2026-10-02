=== Netmilk — WPML Elementor Component Fix ===
Contributors: enuzzo
Tags: wpml, elementor, translation, components, xliff
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A temporary compatibility adapter for explicit Elementor V4 component text overrides missing from WPML export/import.

== Description ==
Adapts escaped-html overrides through the installed WPML native handler. Preserves identifiers, link handling and stored value types. Checks native extraction and import with plain text and HTML; defers when both work. Unknown native contracts are left unchanged.

No settings, database tables, independent translated-page writes, frontend assets, network calls or telemetry. Inherited component defaults are outside its scope. Fresh WPML jobs and a real export/import/render check are required.

Documentation and source: https://github.com/enuzzo/wpml-elementor-component-fix
Independent community project, not affiliated with WPML or Elementor. No WordPress.org directory listing is claimed.

== Installation ==
1. Download the installable ZIP from the GitHub Releases page.
2. Upload through Plugins > Add New > Upload Plugin and activate.
3. Test explicit overrides on a draft page using fresh WPML translation jobs.
4. Verify the XLIFF source fields, import translated targets through WPML and inspect the public translated page.

== Removal ==
After updating WPML/Elementor, deactivate this plugin on a test site and verify a fresh export/import/render cycle. If the native cycle works for the fields you use, delete the plugin. It owns no stored data. Previously missing fields can return in future jobs if native support remains broken.

== Changelog ==
= 1.0.1 =
First public release with conservative native-contract guards, synthetic tests and reproducible packaging.
