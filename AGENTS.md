# Contributor instructions

This is a standalone, public WordPress compatibility plugin.
- Keep the scope limited to Elementor V4 component `escaped-html` overrides handled through WPML.
- Do not write translated Elementor data directly, bypass WPML, or add site-specific IDs or URLs.
- Never add client exports, vendor source code, credentials or private test fixtures. Public doubles must be original, synthetic implementations.
- Preserve the native-support probe and conservative handling of unknown contracts.
- Run `php tests/run.php` and `python3 scripts/build.py` before release.
- Distinguish synthetic contract tests from tests against an installed WordPress/WPML/Elementor stack.
- Update the changelog and version consistently. Release ZIPs must contain one installable plugin folder, without repository tooling.
