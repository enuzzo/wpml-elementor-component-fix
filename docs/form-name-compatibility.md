# V4 form-name compatibility — 1.0.2 candidate

## Diagnosis

On 2026-10-02, the [official remote Elementor configuration](https://cdn.wpml.org/wpml-config/elementor/wpml-config.xml) registered `e-form`'s name at `form-name`. A wrapped string has its text one level deeper:

```php
// Invented example; no client export or vendor implementation.
$element = [
    'id' => 'synthetic-form',
    'widgetType' => 'e-form',
    'settings' => [
        'form-name' => [ '$$type' => 'string', 'value' => 'Synthetic test form' ],
    ],
];
```

The flat path selects an array in this example. The candidate adds the string leaf `form-name>value` while retaining `form-name` as the field identity. WPML documents [nested field paths](https://wpml.org/documentation/support/language-configuration-files/how-to-register-page-builder-widgets-for-translation/) and [the `field_id` registration option](https://wpml.org/documentation/plugins-compatibility/elementor/how-to-add-wpml-support-to-custom-elementor-widgets/). Its public Elementor configuration already uses alternative paths sharing an identity for other V4 widgets.

The configuration mismatch is confirmed. Successful extraction and import on an installed WPML stack are separate evidence and remain unverified for this candidate.

## Narrow intervention

- Require an existing `e-form` registration and exactly one recognizable flat name field. Never manufacture a missing widget registration.
- Append the nested path with `field_id` `form-name`; retain the scalar path for older data and preserve native label/editor metadata.
- Leave existing nested paths untouched, whether supplied by WPML or an addon. Do not add duplicate registrations on repeat calls.
- Leave custom integration classes, repeaters, conflicting identities, changed conditions and unknown configuration shapes untouched.
- Leave all reading, string identifiers and import to WPML. No post/meta writes, translation engine, settings or network calls are introduced.

This is a **configuration-level native-support check**. It cannot detect every future internal WPML change while the flat registration remains identical. It is not the runtime extraction/import probe used for component overrides. Recheck the real cycle after WPML/Elementor updates; prefer removing the workaround once native support works.

## Validation and acceptance

The public suite adds five isolated scenarios with an original field-path double. It covers scalar and wrapped names, distinct instance identities, empty strings and `0`, punctuation/markup, preserved wrapper metadata, immutable sources, repeated filtering/import, existing nested registrations, conservative fallback and coexistence with component overrides. The double's identity format is invented; it is not WPML code or an assertion about WPML's serialization.

A separately authorized integration test still needs to:

1. Record the actual plugin and CMS versions; preserve backup and rollback under the site's own rules.
2. Test two forms with distinct names and both scalar/wrapped data where that stack supports them.
3. Generate fresh local-translator jobs and confirm one expected name segment per form in XLIFF, with stable native identities.
4. Translate targets and import through WPML; verify the wrapped type, translated rendered form name and unrelated form settings/actions.
5. Change a source name and repeat with a fresh job; check that fields do not duplicate and translations remain associated with the right form.
6. Compare with the adapter deactivated to determine whether native support now suffices. Inspect any local addon separately before retiring it.

The repository session does not install on sites, submit forms or claim a completed live cycle. The published 1.0.1 tag and artifacts stay immutable. Version 1.0.2 is a local candidate until separately approved for publication.

## Local verification — 2026-10-02

PHP 8.5.11 passed lint on all eight PHP files and all 20 isolated scenarios, including 96 form assertions. Python 3.14.7 built the candidate and verified its exact three-file payload. The original component filter is byte-for-byte unchanged from v1.0.1. The new candidate has no remote CI result yet; the existing green PHP matrix belongs to the published baseline.

Candidate ZIP: `dist/netmilk-wpml-component-compat-1.0.2.zip`.
SHA-256: `9491879a9b14b00b088ec59db1e79d772cac524b7a2fad7d39fd8f5b1c031c41`.
