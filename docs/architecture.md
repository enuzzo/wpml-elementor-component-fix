# How the temporary adapter works

The entrypoint registers four independent filters on `wpml_elementor_widgets_to_translate` at priority 100. The component filter acts only when the `e-component` integration still names the installed native `WPML\PB\Elementor\V4\Component\Overrides` class and `WPML_PB_String` is available. Separate filters supplement the known form-name registration, attach a narrowly scoped native delegate for direct exposed heading/paragraph origins, and route the known select-item registration through a native delegate. Unrelated widgets and other integration handlers are preserved. Version 1.0.5 adds one frontend class-label filter, described below; the four translation filters are unchanged.

## Native-first decision

A per-request check builds a synthetic override in memory, extracts it with native `get`, and imports a synthetic translation with native `update`. It checks plain text first, then text containing HTML, an entity and punctuation if the first round trip succeeds. The comparison expects the original override item with only its textual value changed. If both probes pass, the original configuration is returned.

Completed support decisions are cached within the request. If native code throws, the adapter makes no change and leaves the decision unset, so a later filter invocation may retry. If the probe demonstrates missing support, reflection checks the known inheritance contract: a non-final parent, public non-static/non-final `get` and `update`, three compatible parameters, and no return-type declarations. Parameters must be untyped except for the third `update` parameter, which must be a non-nullable `WPML_PB_String`; references and variadic parameters are rejected. Missing methods and incompatible signatures are left alone.

These probes check a small known format; they do not certify all component fields or future versions. The native class's naming/update behavior remains authoritative.

## Temporary type conversion

The subclass turns string-valued `escaped-html` overrides into `string` **in a copy** passed to the parent's `get`/`update`. The native handler supplies the identifiers and translated override item. When an original item was `escaped-html`, the subclass restores that type in the returned item. WPML then applies its normal import workflow.

The translation adapters add no database API, post/meta write, translation engine, independent destination synchronization, CSS or JavaScript. General inherited-default resolution, assets, other form fields/actions and other component value types remain outside their scope. The separate 1.0.5 frontend fallback reads kit metadata but never writes it.

## Form-name registration (included in 1.0.4)

This independent filter adds `form-name>value` with `field_id` `form-name` only alongside a single known flat `form-name` registration. It copies the existing field label and optional `LINE` editor metadata and leaves the scalar path intact, following the alternative-path pattern in WPML's public Elementor configuration. It does not read, flatten or write Elementor data; the native field machinery owns extraction and import.

Any existing `form-name>` path, conflicting identity, custom integration, repeater or unknown widget/field configuration prevents the addition. Repeated filtering adds nothing further. These are configuration guards, not runtime native form extraction/import probes: component overrides and master origins have separate runtime probes. See [the diagnosis and validation limits](form-name-compatibility.md).

## Master-origin delegation (included in 1.0.4)

For recognized heading/paragraph field configurations, a supplemental integration composes `WPML_Elementor_Translatable_Nodes` with only the original text fields. Native public methods own extraction, names, active-field rules and updates. A request-local registration guard supplies that scoped configuration without recursively registering the adapter. No alternative origin field paths are added.

The module checks plain-text and HTML round trips from the native integration callback, after ordinary field processing, retaining the actual element context. Probing an invented widget during registration could seed the native active-settings cache with the wrong element. Completed support decisions are cached by widget/configuration; exceptions leave them unset. If native support passes, the supplemental module returns incoming strings or no update and leaves native processing in charge.

When support is missing, only a recognized direct escaped-html origin is converted to string in a copy. Extraction appends native strings with matching names and exact source values, skipping identities already present. Import is accepted only if the delegated result equals the complete copied element with just that text changed; the original type is restored before returning the item for WPML to apply. Unknown contracts, custom handlers, existing origin paths and malformed or forwarded values remain untouched. Empty text and literal `"0"` keep the native omission behavior.

The component and form filters are unchanged from 1.0.2. The master adapter does not synchronize the property registry, map component IDs or materialize instance overrides. See [master-origin diagnosis and acceptance](master-origin-compatibility.md).

## Select-option delegation (1.0.4)

A guarded filter replaces only the recognized `e-form-select` item registration with a supplemental module composing the same native item handler. Original ordinary fields remain. Unknown/custom configurations are left unchanged. Runtime probes check distinct native identities, plain/HTML/zero labels and exact item import; successful support uses the original native handler with unmodified data.

For the known missing support, the copy exposes the nested collection through its literal registered key and supplies temporary item IDs derived from unique, nonempty technical values. WPML generates the actual string names. A collision-free placeholder lets native extraction include a zero label, then its returned string is restored to `"0"`. Blank labels remain omitted. Neither label text nor array position determines the surrogate ID.

Both extraction and import require unique native identities. Import accepts only the expected item with its visible label changed, strips the temporary item ID, and lets the native outer dispatcher write the original nested collection path. Technical values, wrappers and metadata stay intact. Copies, probes and error-handler restoration are request-local; no metadata synchronization is added. See [select-option contracts and acceptance](select-option-compatibility.md).

## Frontend global-class labels (1.0.5)

At priority 100 on `elementor/atomic-widgets/settings/transformers/classes`, inspect only global IDs still present after native resolution. Require a non-default frontend language, a published translated Kit and a bidirectional WPML `elementor_library` mapping to the published kit named by `elementor_active_kit`. Admin, WordPress/Elementor preview, REST, AJAX and CLI contexts are excluded.

Construct an isolated source Kit with its explicit post ID and verify the resulting identity. Do not use the document manager, which can redirect the source ID to its translation. An explicit source repository in frontend mode supplies ordered labels. Target labels, order and class post IDs each establish ownership and prevent fallback, even if incomplete. Already resolved names, local classes, input keys/order and unknown IDs remain unchanged. Ambiguous or colliding labels, and names outside the conservative ASCII CSS identifier subset, remain native.

The filter reads only the small label/order maps; it does not load full class definitions, call repository migration/cleanup methods, write metadata, regenerate CSS, switch language or replace the active kit. Native errors return the input, and a `finally`-protected reentry guard prevents constructor callbacks from recursing. No support decision or label map is shared across rendering contexts. Loaded stylesheet selectors and computed styles require live acceptance: identity checks do not prove that CSS was delivered correctly. See the [contract and live gates](global-class-rendering.md#105-contract).

## Tests and limitations

The public harness consists of original synthetic doubles. It tests the adapter's behavior against a minimal invented contract; it does not ship or load WPML code.

- Four invented instances verify recovered texts, original markup, legacy text/HTML, link metadata, unique field identities, reordering and source immutability.
- Separate processes test a broken handler, a working handler, exceptions, broken import and text-only native support.
- Future-contract cases cover final classes/methods, typed returns/parameters, references, missing/static methods, changed parameter count and absent handlers.
- Five additional form scenarios use an original synthetic field-path model to check scalar/wrapped names, identity, metadata, source immutability, repeat import, existing native registrations, unknown configurations and coexistence. They do not execute WPML's actual form importer.
- Twelve master-origin scenarios use an original node-handler model to check native delegation, plain/HTML probes, unsafe updates, duplicate prevention, active fields/cache, unknown configurations/signatures and coexistence. A separate local diagnostic loads the installed native handler source with original environment stubs, outside the public repository. Neither test layer verifies WordPress jobs, registry synchronization or inherited rendering.

- Twelve select scenarios cover broken/fixed native behavior, partial support, collisions, errors, unknown signatures/configurations, technical-value preservation, zero/blank labels, reordered items and source edits. A separate 20-check diagnostic exercises the installed native item handler and outer dispatcher with simulated dependencies.

Run a real draft export/import/render cycle before production use and again after dependency updates. Record actual version combinations in a reproducible issue without sharing vendor code or client content.
