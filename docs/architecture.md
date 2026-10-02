# How the temporary adapter works

The entrypoint registers two independent filters on `wpml_elementor_widgets_to_translate` at priority 100. The component filter acts only when the `e-component` integration still names the installed native `WPML\PB\Elementor\V4\Component\Overrides` class and `WPML_PB_String` is available. The 1.0.2 candidate's second filter supplements one known `e-form` name registration. Unrelated widgets and other integration handlers are preserved.

## Native-first decision

A per-request check builds a synthetic override in memory, extracts it with native `get`, and imports a synthetic translation with native `update`. It checks plain text first, then text containing HTML, an entity and punctuation if the first round trip succeeds. The comparison expects the original override item with only its textual value changed. If both probes pass, the original configuration is returned.

Completed support decisions are cached within the request. If native code throws, the adapter makes no change and leaves the decision unset, so a later filter invocation may retry. If the probe demonstrates missing support, reflection checks the known inheritance contract: a non-final parent, public non-static/non-final `get` and `update`, three compatible parameters, and no return-type declarations. Parameters must be untyped except for the third `update` parameter, which must be a non-nullable `WPML_PB_String`; references and variadic parameters are rejected. Missing methods and incompatible signatures are left alone.

These probes check a small known format; they do not certify all component fields or future versions. The native class's naming/update behavior remains authoritative.

## Temporary type conversion

The subclass turns string-valued `escaped-html` overrides into `string` **in a copy** passed to the parent's `get`/`update`. The native handler supplies the identifiers and translated override item. When an original item was `escaped-html`, the subclass restores that type in the returned item. WPML then applies its normal import workflow.

No database API, post/meta write, translation engine, independent destination synchronization, CSS or JavaScript is added. Inherited defaults, assets, other form fields/actions and other component value types remain outside the adapter's scope.

## Form-name registration (1.0.2 candidate)

This independent filter adds `form-name>value` with `field_id` `form-name` only alongside a single known flat `form-name` registration. It copies the existing field label and optional `LINE` editor metadata and leaves the scalar path intact, following the alternative-path pattern in WPML's public Elementor configuration. It does not read, flatten or write Elementor data; the native field machinery owns extraction and import.

Any existing `form-name>` path, conflicting identity, custom integration, repeater or unknown widget/field configuration prevents the addition. Repeated filtering adds nothing further. These are configuration guards, not runtime native form extraction/import probes: only the component adapter has those probes. See [the diagnosis and validation limits](form-name-compatibility.md).

## Tests and limitations

The public harness consists of original synthetic doubles. It tests the adapter's behavior against a minimal invented contract; it does not ship or load WPML code.

- Four invented instances verify recovered texts, original markup, legacy text/HTML, link metadata, unique field identities, reordering and source immutability.
- Separate processes test a broken handler, a working handler, exceptions, broken import and text-only native support.
- Future-contract cases cover final classes/methods, typed returns/parameters, references, missing/static methods, changed parameter count and absent handlers.
- Five additional form scenarios use an original synthetic field-path model to check scalar/wrapped names, identity, metadata, source immutability, repeat import, existing native registrations, unknown configurations and coexistence. They do not execute WPML's actual form importer.

Run a real draft export/import/render cycle before production use and again after dependency updates. Record actual version combinations in a reproducible issue without sharing vendor code or client content.
