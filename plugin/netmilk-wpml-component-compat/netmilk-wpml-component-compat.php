<?php
/**
 * Plugin Name: Netmilk — WPML Elementor Component Fix
 * Description: Native WPML text compatibility and a guarded frontend global-class fallback for translated Elementor V4 kits.
 * Version: 1.0.5
 * Requires PHP: 7.4
 * Requires at least: 6.5
 * Plugin URI: https://github.com/enuzzo/wpml-elementor-component-fix
 * Update URI: https://github.com/enuzzo/wpml-elementor-component-fix
 * Author: Netmilk Studio
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Text Domain: netmilk-wpml-component-compat
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'wpml_elementor_widgets_to_translate', function ( $widgets ) {
    $native = 'WPML\\PB\\Elementor\\V4\\Component\\Overrides';
    if ( ! is_array( $widgets ) || ! isset( $widgets['e-component']['integration-class'] )
        || ! class_exists( $native ) || ! class_exists( 'WPML_PB_String' ) ) {
        return $widgets;
    }
    $classes = (array) $widgets['e-component']['integration-class'];
    $positions = [];
    foreach ( $classes as $position => $class ) {
        if ( is_string( $class ) && ltrim( $class, '\\' ) === $native ) {
            $positions[] = $position;
        }
    }
    if ( ! $positions ) {
        return $widgets;
    }

    // Defer to the official implementation once BOTH extraction and import work.
    static $native_supports = null;
    if ( null === $native_supports ) {
        $native_supports = true;
        try {
            foreach ( [ 'Netmilk compatibility probe', 'Netmilk <strong>HTML</strong> &amp; apostrophe ’ probe' ] as $source ) {
                $probe = [
                    'widgetType' => 'e-component',
                    'settings' => [
                        'component_instance' => [
                            'value' => [
                                'overrides' => [
                                    'value' => [
                                        [
                                            '$$type' => 'override',
                                            'value' => [
                                                'override_key' => 'netmilk-component-compat-probe',
                                                'override_value' => [ '$$type' => 'escaped-html', 'value' => $source ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
                $handler = new $native();
                $strings = $handler->get( 'netmilk-component-compat-probe', $probe, [] );
                $roundtrip_ok = false;
                foreach ( $strings as $string ) {
                    if ( $source !== $string->get_value() ) {
                        continue;
                    }
                    $target = 'Translated: ' . $source;
                    $translation = new WPML_PB_String( $target, $string->get_name(), 'Compatibility probe', 'LINE' );
                    [ $key, $item ] = $handler->update( 'netmilk-component-compat-probe', $probe, $translation );
                    $expected = $probe['settings']['component_instance']['value']['overrides']['value'][0];
                    $expected['value']['override_value']['value'] = $target;
                    $roundtrip_ok = 0 === $key && $item === $expected;
                    break;
                }
                if ( ! $roundtrip_ok ) {
                    $native_supports = false;
                    break;
                }
            }
        } catch ( Throwable $error ) {
            // An unknown native contract is not safe to override.
            $native_supports = null;
            return $widgets;
        }
    }
    if ( $native_supports ) {
        return $widgets;
    }

    // Inheritance must match the known WPML contract. Future API changes are
    // left untouched instead of risking a fatal error on plugin activation.
    $reflection = new ReflectionClass( $native );
    if ( $reflection->isFinal() ) {
        return $widgets;
    }
    foreach ( [ 'get', 'update' ] as $method_name ) {
        if ( ! $reflection->hasMethod( $method_name ) ) {
            return $widgets;
        }
        $method = $reflection->getMethod( $method_name );
        if ( $method->isFinal() || $method->isStatic() || ! $method->isPublic()
            || $method->hasReturnType() || 3 !== $method->getNumberOfParameters() ) {
            return $widgets;
        }
        foreach ( $method->getParameters() as $index => $parameter ) {
            $expected_type = 'update' === $method_name && 2 === $index ? 'WPML_PB_String' : '';
            $type = $parameter->getType();
            $type_matches = '' === $expected_type
                ? null === $type
                : $type instanceof ReflectionNamedType && ! $type->allowsNull() && $type->getName() === $expected_type;
            if ( ! $type_matches || $parameter->isPassedByReference() || $parameter->isVariadic() ) {
                return $widgets;
            }
        }
    }

    if ( ! class_exists( 'Netmilk_WPML_Escaped_HTML_Overrides', false ) ) {
        class Netmilk_WPML_Escaped_HTML_Overrides extends \WPML\PB\Elementor\V4\Component\Overrides {
            private function compatible_element( $element ) {
                $items = $element['settings']['component_instance']['value']['overrides']['value'] ?? [];
                if ( ! is_array( $items ) ) {
                    return $element;
                }
                foreach ( $items as $key => $item ) {
                    $value = $item['value']['override_value'] ?? [];
                    if ( 'escaped-html' === ( $value['$$type'] ?? null ) && is_string( $value['value'] ?? null ) ) {
                        // This is a temporary in-memory copy, never the saved Elementor data.
                        $element['settings']['component_instance']['value']['overrides']['value'][ $key ]['value']['override_value']['$$type'] = 'string';
                    }
                }
                return $element;
            }

            public function get( $node_id, $element, $strings ) {
                return parent::get( $node_id, $this->compatible_element( $element ), $strings );
            }

            public function update( $node_id, $element, \WPML_PB_String $string ) {
                [ $key, $item ] = parent::update( $node_id, $this->compatible_element( $element ), $string );
                if ( null === $key || ! is_array( $item ) ) {
                    return [ $key, $item ];
                }
                $original = $element['settings']['component_instance']['value']['overrides']['value'][ $key ]['value']['override_value'] ?? [];
                if ( 'escaped-html' === ( $original['$$type'] ?? null ) && is_string( $original['value'] ?? null ) ) {
                    $item['value']['override_value']['$$type'] = 'escaped-html';
                }
                return [ $key, $item ];
            }
        }
    }
    foreach ( $positions as $position ) {
        $classes[ $position ] = 'Netmilk_WPML_Escaped_HTML_Overrides';
    }
    $widgets['e-component']['integration-class'] = $classes;
    return $widgets;
}, 100 );

// Compose the native node handler instead of replacing its private path logic.
// All state is request-local; the narrow delegated config prevents recursion.
class Netmilk_WPML_Master_Origins {
    private static $delegated = null;
    private static $configs = [];
    private static $support = [];

    private static function known_contract() {
        if ( ! class_exists( 'WPML_Elementor_Translatable_Nodes' ) || ! class_exists( 'WPML_PB_String' ) ) {
            return false;
        }
        $class = new ReflectionClass( 'WPML_Elementor_Translatable_Nodes' );
        $constructor = $class->getConstructor();
        if ( ! $class->isInstantiable() || ( $constructor && $constructor->getNumberOfRequiredParameters() ) ) {
            return false;
        }
        foreach ( [ 'get' => 2, 'update' => 3, 'get_string_name' => 3, 'initialize_nodes_to_translate' => 0 ] as $name => $count ) {
            if ( ! $class->hasMethod( $name ) ) {
                return false;
            }
            $method = $class->getMethod( $name );
            if ( ! $method->isPublic() || $method->isStatic() || $method->hasReturnType()
                || $method->getNumberOfParameters() !== $count ) {
                return false;
            }
            foreach ( $method->getParameters() as $index => $parameter ) {
                $type = $parameter->getType();
                $typed_string = 'update' === $name && 2 === $index;
                $matches = $typed_string
                    ? $type instanceof ReflectionNamedType && ! $type->allowsNull() && 'WPML_PB_String' === $type->getName()
                    : null === $type;
                if ( ! $matches || $parameter->isPassedByReference() || $parameter->isVariadic() ) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function handler( $widget ) {
        $previous = self::$delegated;
        self::$delegated = [ $widget => self::$configs[ $widget ] ];
        try {
            $handler = new WPML_Elementor_Translatable_Nodes();
            $handler->initialize_nodes_to_translate();
            return $handler;
        } finally {
            self::$delegated = $previous;
        }
    }

    private static function property( $element ) {
        $properties = [ 'e-heading' => 'title', 'e-paragraph' => 'paragraph' ];
        $widget = $element['widgetType'] ?? null;
        return is_string( $widget ) ? ( $properties[ $widget ] ?? null ) : null;
    }

    private static function compatible( $element, $property ) {
        $element['settings'][ $property ]['value']['origin_value']['$$type'] = 'string';
        return $element;
    }

    private static function supported_element( $element, $property ) {
        if ( ! $property || ! isset( self::$configs[ $element['widgetType'] ] ) ) {
            return false;
        }
        $wrapper = $element['settings'][ $property ] ?? null;
        return is_array( $wrapper ) && 'overridable' === ( $wrapper['$$type'] ?? null )
            && is_string( $wrapper['value']['override_key'] ?? null )
            && '' !== $wrapper['value']['override_key']
            && 'escaped-html' === ( $wrapper['value']['origin_value']['$$type'] ?? null )
            && is_string( $wrapper['value']['origin_value']['value'] ?? null );
    }

    private static function matching_names( $handler, $node_id, $element ) {
        $names = [];
        foreach ( self::$configs[ $element['widgetType'] ]['fields'] as $field ) {
            $names[] = $handler->get_string_name( $node_id, $field, $element );
        }
        return $names;
    }

    private static function native_support( $widget, $property, $element, $node_id ) {
        $key = $widget . ':' . md5( serialize( self::$configs[ $widget ] ) );
        if ( array_key_exists( $key, self::$support ) ) {
            return self::$support[ $key ];
        }
        $handler = self::handler( $widget );
        foreach ( [ 'Synthetic master probe', 'Synthetic <strong>master</strong> &amp; café ’' ] as $source ) {
            // Run only from the native integration callback, after native field
            // processing. Keep the actual element context: probing an invented
            // widget during registration could poison WPML's active-settings cache.
            $node = $element;
            $node['settings'][ $property ]['value']['origin_value']['value'] = $source;
            $names = self::matching_names( $handler, $node_id, $node );
            $roundtrip = false;
            $strings = $handler->get( $node_id, $node );
            if ( ! is_array( $strings ) ) {
                throw new UnexpectedValueException( 'Unknown native master extraction contract' );
            }
            foreach ( $strings as $string ) {
                if ( ! $string instanceof WPML_PB_String || $source !== $string->get_value()
                    || ! in_array( $string->get_name(), $names, true ) ) {
                    continue;
                }
                $target = 'Translated: ' . $source;
                $translation = new WPML_PB_String( $target, $string->get_name(), 'Synthetic master probe', 'LINE' );
                $expected = $node;
                $expected['settings'][ $property ]['value']['origin_value']['value'] = $target;
                $roundtrip = $handler->update( $node_id, $node, $translation ) === $expected;
                break;
            }
            if ( ! $roundtrip ) {
                self::$support[ $key ] = false;
                return false;
            }
        }
        self::$support[ $key ] = true;
        return true;
    }

    public static function register( $widgets ) {
        if ( null !== self::$delegated ) {
            return self::$delegated;
        }
        if ( ! is_array( $widgets ) || ! self::known_contract() ) {
            return $widgets;
        }
        foreach ( [ 'e-heading' => 'title', 'e-paragraph' => 'paragraph' ] as $widget => $property ) {
            $config = $widgets[ $widget ] ?? null;
            if ( ! is_array( $config ) || ! isset( $config['fields'] ) || ! is_array( $config['fields'] )
                || ( $config['conditions'] ?? null ) !== [ 'widgetType' => $widget ]
                || array_diff( array_keys( $config ), [ 'fields', 'conditions' ] ) ) {
                continue;
            }
            $plain = $property . '>value';
            $legacy = $plain . '>content>value';
            $fields = [];
            $paths = [];
            $safe = true;
            foreach ( $config['fields'] as $field ) {
                if ( ! is_array( $field ) || ! isset( $field['field'] ) || ! is_string( $field['field'] ) ) {
                    $safe = false;
                    break;
                }
                $path = $field['field'];
                if ( $path === $property || 0 === strpos( $path, $property . '>' ) ) {
                    if ( ! in_array( $path, [ $plain, $legacy ], true ) || isset( $paths[ $path ] )
                        || array_diff( array_keys( $field ), [ 'field', 'field_id', 'type', 'editor_type' ] )
                        || ! is_string( $field['type'] ?? null ) || '' === $field['type']
                        || ! in_array( $field['editor_type'] ?? null, [ 'LINE', 'AREA', 'VISUAL' ], true )
                        || ( array_key_exists( 'field_id', $field ) && ! in_array( $field['field_id'], [ $path, $legacy ], true ) ) ) {
                        $safe = false;
                        break;
                    }
                    $paths[ $path ] = true;
                    $fields[] = $field;
                } elseif ( isset( $field['field_id'] ) && in_array( $field['field_id'], [ $plain, $legacy ], true ) ) {
                    $safe = false;
                    break;
                }
            }
            if ( ! $safe || ! $fields ) {
                continue;
            }
            // Only original text fields enter the delegate; no integration recursion.
            self::$configs[ $widget ] = [ 'conditions' => $config['conditions'], 'fields' => $fields ];
            $widgets[ $widget ]['integration-class'] = [ __CLASS__ ];
        }
        return $widgets;
    }

    public function get( $node_id, $element, $strings ) {
        $property = self::property( $element );
        if ( ! self::supported_element( $element, $property ) || ! is_array( $strings ) ) {
            return $strings;
        }
        $original_strings = $strings;
        try {
            if ( self::native_support( $element['widgetType'], $property, $element, $node_id ) ) {
                return $strings;
            }
            $handler = self::handler( $element['widgetType'] );
            $names = self::matching_names( $handler, $node_id, $element );
            $extracted = $handler->get( $node_id, self::compatible( $element, $property ) );
            if ( ! is_array( $extracted ) ) {
                return $strings;
            }
            foreach ( $extracted as $string ) {
                if ( ! $string instanceof WPML_PB_String || ! in_array( $string->get_name(), $names, true )
                    || $string->get_value() !== $element['settings'][ $property ]['value']['origin_value']['value'] ) {
                    continue;
                }
                $exists = false;
                foreach ( $strings as $existing ) {
                    if ( $existing instanceof WPML_PB_String && $existing->get_name() === $string->get_name() ) {
                        $exists = true;
                        break;
                    }
                }
                if ( ! $exists ) {
                    $strings[] = $string;
                }
            }
        } catch ( Throwable $error ) {
            return $original_strings;
        }
        return $strings;
    }

    public function update( $node_id, $element, WPML_PB_String $string ) {
        $property = self::property( $element );
        if ( ! self::supported_element( $element, $property ) || ! is_string( $string->get_value() ) ) {
            return [ null, null ];
        }
        try {
            if ( self::native_support( $element['widgetType'], $property, $element, $node_id ) ) {
                return [ null, null ];
            }
            $handler = self::handler( $element['widgetType'] );
            if ( ! in_array( $string->get_name(), self::matching_names( $handler, $node_id, $element ), true ) ) {
                return [ null, null ];
            }
            $copy = self::compatible( $element, $property );
            $result = $handler->update( $node_id, $copy, $string );
            $expected = $copy;
            $expected['settings'][ $property ]['value']['origin_value']['value'] = $string->get_value();
            if ( $result !== $expected ) {
                return [ null, null ];
            }
            $result['settings'][ $property ]['value']['origin_value']['$$type'] = 'escaped-html';
            return [ $property . '>value', $result['settings'][ $property ]['value'] ];
        } catch ( Throwable $error ) {
            return [ null, null ];
        }
    }

    public function get_field_path( $key ) {
        return explode( '>', $key );
    }
}
add_filter( 'wpml_elementor_widgets_to_translate', [ 'Netmilk_WPML_Master_Origins', 'register' ], 100 );

// Extend only the known flat form-name registration. WPML owns all data access,
// string identities and import; the scalar path remains available for old data.
add_filter( 'wpml_elementor_widgets_to_translate', function ( $widgets ) {
    if ( ! is_array( $widgets ) || ! isset( $widgets['e-form'] ) || ! is_array( $widgets['e-form'] ) ) {
        return $widgets;
    }
    $form = $widgets['e-form'];
    if ( ! isset( $form['fields'] ) || ! is_array( $form['fields'] )
        || ( array_key_exists( 'conditions', $form ) && [ 'widgetType' => 'e-form' ] !== $form['conditions'] )
        || array_diff( array_keys( $form ), [ 'fields', 'conditions' ] ) ) {
        // Integration classes, repeaters and unknown widget contracts are left alone.
        return $widgets;
    }
    $candidate = null;
    foreach ( $form['fields'] as $field ) {
        if ( ! is_array( $field ) || ! isset( $field['field'] ) || ! is_string( $field['field'] ) ) {
            return $widgets;
        }
        $path = $field['field'];
        if ( 0 === strpos( $path, 'form-name>' ) ) {
            // Native support or another adapter already owns a nested name path.
            return $widgets;
        }
        if ( 'form-name' !== $path ) {
            if ( isset( $field['field_id'] ) && 'form-name' === $field['field_id'] ) {
                return $widgets;
            }
            continue;
        }
        if ( null !== $candidate
            || array_diff( array_keys( $field ), [ 'field', 'field_id', 'type', 'editor_type' ] )
            || ! isset( $field['type'] ) || ! is_string( $field['type'] ) || '' === $field['type']
            || ( array_key_exists( 'field_id', $field ) && 'form-name' !== $field['field_id'] )
            || ( array_key_exists( 'editor_type', $field ) && 'LINE' !== $field['editor_type'] ) ) {
            return $widgets;
        }
        $candidate = $field;
    }
    if ( null !== $candidate ) {
        $candidate['field'] = 'form-name>value';
        $candidate['field_id'] = 'form-name';
        $widgets['e-form']['fields'][] = $candidate;
    }
    return $widgets;
}, 100 );

// Adapt the recognized V4 select collection to the native item handler. Synthetic
// item IDs and the literal collection alias exist only in the delegated copy.
class Netmilk_WPML_Select_Options {
    const ITEMS = 'options>value';
    const LABEL = 'value>key>value';
    const NATIVE_CLASS = 'WPML\\PB\\Elementor\\Modules\\ModuleWithItemsFromConfig';
    private static $fields = [];
    private static $support = [];

    private static function known_contract() {
        if ( ! class_exists( self::NATIVE_CLASS ) || ! class_exists( 'WPML_PB_String' ) ) {
            return false;
        }
        $class = new ReflectionClass( self::NATIVE_CLASS );
        if ( ! $class->isInstantiable() ) {
            return false;
        }
        foreach ( [ '__construct' => 2, 'get' => 3, 'update' => 3, 'get_items' => 1,
            'get_items_field' => 0, 'get_field_path' => 1, 'get_title' => 1,
            'get_editor_type' => 1, 'get_fields' => 0 ] as $name => $count ) {
            if ( ! $class->hasMethod( $name ) ) {
                return false;
            }
            $method = $class->getMethod( $name );
            if ( ! $method->isPublic() || $method->isStatic() || $method->hasReturnType()
                || $method->getNumberOfParameters() !== $count ) {
                return false;
            }
            foreach ( $method->getParameters() as $index => $parameter ) {
                $expected = '__construct' === $name && 1 === $index ? 'array'
                    : ( 'update' === $name && 2 === $index ? 'WPML_PB_String' : null );
                $type = $parameter->getType();
                $matches = null === $expected ? null === $type
                    : $type instanceof ReflectionNamedType && ! $type->allowsNull() && $type->getName() === $expected;
                if ( ! $matches || $parameter->isPassedByReference() || $parameter->isVariadic() ) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function register( $widgets ) {
        $config = is_array( $widgets ) ? ( $widgets['e-form-select'] ?? null ) : null;
        if ( ! is_array( $config ) || ! self::known_contract()
            || ( $config['conditions'] ?? null ) !== [ 'widgetType' => 'e-form-select' ]
            || ! is_array( $config['fields'] ?? null )
            || array_diff( array_keys( $config ), [ 'conditions', 'fields', 'fields_in_item' ] )
            || ! is_array( $config['fields_in_item'] ?? null )
            || array_keys( $config['fields_in_item'] ) !== [ self::ITEMS ] ) {
            return $widgets;
        }
        foreach ( $config['fields'] as $field ) {
            if ( ! is_array( $field ) || ! is_string( $field['field'] ?? null )
                || 'options' === $field['field'] || 0 === strpos( $field['field'], 'options>' ) ) {
                return $widgets;
            }
        }
        $fields = $config['fields_in_item'][ self::ITEMS ];
        if ( ! is_array( $fields ) || array_keys( $fields ) !== [ 0 ] ) {
            return $widgets;
        }
        $field = $fields[0];
        if ( ! is_array( $field ) || ( $field['field'] ?? null ) !== self::LABEL
            || ! is_string( $field['type'] ?? null ) || '' === $field['type']
            || ( $field['editor_type'] ?? null ) !== 'LINE'
            || array_diff( array_keys( $field ), [ 'field', 'type', 'editor_type' ] ) ) {
            return $widgets;
        }
        self::$fields = $fields;
        unset( $widgets['e-form-select']['fields_in_item'] );
        $widgets['e-form-select']['integration-class'] = [ __CLASS__ ];
        return $widgets;
    }

    private static function handler() {
        $class = self::NATIVE_CLASS;
        return new $class( self::ITEMS, self::$fields );
    }

    private static function items( $element ) {
        $options = $element['settings']['options'] ?? null;
        if ( ( $element['widgetType'] ?? null ) !== 'e-form-select'
            || ! is_array( $element['settings'] ?? null )
            || array_key_exists( self::ITEMS, $element['settings'] )
            || ! is_array( $options ) || ( $options['$$type'] ?? null ) !== 'options'
            || ! is_array( $options['value'] ?? null ) ) {
            return null;
        }
        $items = $options['value'];
        if ( $items && array_keys( $items ) !== range( 0, count( $items ) - 1 ) ) {
            return null;
        }
        $seen = [];
        foreach ( $items as $item ) {
            $label = $item['value']['key'] ?? null;
            $value = $item['value']['value'] ?? null;
            if ( ! is_array( $item ) || array_key_exists( '_id', $item )
                || ( $item['$$type'] ?? null ) !== 'key-value'
                || ! is_array( $label ) || ( $label['$$type'] ?? null ) !== 'string'
                || ! is_string( $label['value'] ?? null )
                || ! is_array( $value ) || ( $value['$$type'] ?? null ) !== 'string'
                || ! is_string( $value['value'] ?? null ) || '' === $value['value'] ) {
                return null;
            }
            $identity = hash( 'sha256', $value['value'] );
            if ( isset( $seen[ $identity ] ) ) {
                return null;
            }
            $seen[ $identity ] = true;
        }
        return $items;
    }

    private static function compatible( $element, $items, $extract ) {
        $labels = array_map( function ( $item ) { return $item['value']['key']['value']; }, $items );
        $zero_label = 'Netmilk zero-label compatibility value';
        while ( in_array( $zero_label, $labels, true ) ) {
            $zero_label .= '.';
        }
        foreach ( $items as &$item ) {
            $item['_id'] = 'netmilk-select-' . substr( hash( 'sha256', $item['value']['value']['value'] ), 0, 24 );
            if ( $extract && '0' === $item['value']['key']['value'] ) {
                // The native item extractor uses truthiness. Restore the exact
                // zero label on its resulting string, retaining the native name.
                $item['value']['key']['value'] = $zero_label;
            }
        }
        unset( $item );
        $element['settings'][ self::ITEMS ] = $items;
        return $element;
    }

    private static function call_native( $callback ) {
        // Convert probe diagnostics into a bounded failure and always restore the
        // caller's handler. Only the known get_items missing-key error is support
        // evidence; other failures leave the adapter's decision unset.
        set_error_handler( function ( $severity, $message, $file, $line ) {
            throw new ErrorException( $message, 0, $severity, $file, $line );
        } );
        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    private static function missing_collection( ErrorException $error ) {
        $method = new ReflectionMethod( self::NATIVE_CLASS, 'get_items' );
        return in_array( $error->getSeverity(), [ E_NOTICE, E_WARNING ], true )
            && false !== strpos( $error->getMessage(), self::ITEMS )
            && $error->getFile() === $method->getFileName()
            && $error->getLine() >= $method->getStartLine()
            && $error->getLine() <= $method->getEndLine();
    }

    private static function native_support( $handler, $node_id, $element ) {
        $key = md5( serialize( self::$fields ) );
        if ( array_key_exists( $key, self::$support ) ) {
            return self::$support[ $key ];
        }
        if ( is_callable( [ 'WPML_Elementor_Translatable_Nodes', 'get_active_element_settings' ] ) ) {
            // A native active-settings cache, when enabled, must see the real
            // element before the probe substitutes its in-memory option rows.
            self::call_native( function () use ( $element ) {
                return WPML_Elementor_Translatable_Nodes::get_active_element_settings( $element );
            } );
        }
        $probe = $element;
        $items = [];
        foreach ( [ 'Synthetic option', 'Synthetic <b>option</b> &amp; café', '0' ] as $index => $label ) {
            $items[] = [ '$$type' => 'key-value', 'value' => [
                'key' => [ '$$type' => 'string', 'value' => $label ],
                'value' => [ '$$type' => 'string', 'value' => 'netmilk-select-probe-' . $index ],
            ] ];
        }
        $probe['settings']['options']['value'] = $items;
        try {
            $strings = self::call_native( function () use ( $handler, $node_id, $probe ) {
                return $handler->get( $node_id, $probe, [] );
            } );
        } catch ( ErrorException $error ) {
            if ( self::missing_collection( $error ) ) {
                self::$support[ $key ] = false;
                return false;
            }
            throw $error;
        }
        if ( ! is_array( $strings ) ) {
            throw new UnexpectedValueException( 'Unknown native select extraction contract' );
        }
        $works = count( $strings ) === count( $items );
        $names = [];
        foreach ( array_values( $strings ) as $index => $string ) {
            if ( ! isset( $items[ $index ] ) || ! $string instanceof WPML_PB_String
                || $string->get_value() !== $items[ $index ]['value']['key']['value']
                || in_array( $string->get_name(), $names, true ) ) {
                $works = false;
                break;
            }
            $names[] = $string->get_name();
            $target = 'Translated: ' . $string->get_value();
            $translation = new WPML_PB_String( $target, $string->get_name(), self::$fields[0]['type'], 'LINE' );
            $expected = $items[ $index ];
            $expected['value']['key']['value'] = $target;
            $result = self::call_native( function () use ( $handler, $node_id, $probe, $translation ) {
                return $handler->update( $node_id, $probe, $translation );
            } );
            if ( $result !== [ $index, $expected ] ) {
                $works = false;
                break;
            }
        }
        self::$support[ $key ] = $works;
        return $works;
    }

    private static function extracted( $handler, $node_id, $element, $items ) {
        $copy = self::compatible( $element, $items, true );
        $strings = self::call_native( function () use ( $handler, $node_id, $copy ) {
            return $handler->get( $node_id, $copy, [] );
        } );
        $visible = array_filter( $items, function ( $item ) {
            return '' !== $item['value']['key']['value'];
        } );
        if ( ! is_array( $strings ) || count( $strings ) !== count( $visible ) ) {
            return null;
        }
        $strings = array_values( $strings );
        $names = [];
        $result = [];
        foreach ( array_keys( $visible ) as $position => $item_index ) {
            $string = $strings[ $position ];
            $expected = $copy['settings'][ self::ITEMS ][ $item_index ]['value']['key']['value'];
            if ( ! $string instanceof WPML_PB_String || $string->get_value() !== $expected
                || in_array( $string->get_name(), $names, true ) ) {
                return null;
            }
            $names[] = $string->get_name();
            if ( '0' === $items[ $item_index ]['value']['key']['value'] ) {
                $string = new WPML_PB_String( '0', $string->get_name(), $handler->get_title( self::LABEL ), $handler->get_editor_type( self::LABEL ) );
            }
            $result[] = $string;
        }
        return $result;
    }

    public function get( $node_id, $element, $strings ) {
        if ( ! is_array( $strings ) || ! self::$fields ) {
            return $strings;
        }
        try {
            $handler = self::handler();
            $items = self::items( $element );
            if ( null === $items || self::native_support( $handler, $node_id, $element ) ) {
                $result = self::call_native( function () use ( $handler, $node_id, $element, $strings ) {
                    return $handler->get( $node_id, $element, $strings );
                } );
                return is_array( $result ) ? $result : $strings;
            }
            $extracted = self::extracted( $handler, $node_id, $element, $items );
            if ( null === $extracted ) {
                return $strings;
            }
            $result = $strings;
            foreach ( $extracted as $string ) {
                foreach ( $result as $existing ) {
                    if ( $existing instanceof WPML_PB_String && $existing->get_name() === $string->get_name() ) {
                        continue 2;
                    }
                }
                $result[] = $string;
            }
            return $result;
        } catch ( Throwable $error ) {
            return $strings;
        }
    }

    public function update( $node_id, $element, WPML_PB_String $string ) {
        if ( ! self::$fields || ! is_string( $string->get_value() ) ) {
            return [ null, null ];
        }
        try {
            $handler = self::handler();
            $items = self::items( $element );
            if ( null === $items || self::native_support( $handler, $node_id, $element ) ) {
                $result = self::call_native( function () use ( $handler, $node_id, $element, $string ) {
                    return $handler->update( $node_id, $element, $string );
                } );
                return is_array( $result ) && array_keys( $result ) === [ 0, 1 ] ? $result : [ null, null ];
            }
            $extracted = self::extracted( $handler, $node_id, $element, $items );
            $names = array_map( function ( $item ) { return $item->get_name(); }, $extracted ?? [] );
            if ( ! in_array( $string->get_name(), $names, true ) ) {
                return [ null, null ];
            }
            $copy = self::compatible( $element, $items, false );
            $result = self::call_native( function () use ( $handler, $node_id, $copy, $string ) {
                return $handler->update( $node_id, $copy, $string );
            } );
            if ( ! is_array( $result ) || array_keys( $result ) !== [ 0, 1 ]
                || ! is_int( $result[0] ) || ! isset( $items[ $result[0] ] ) ) {
                return [ null, null ];
            }
            list( $key, $item ) = $result;
            $expected = $copy['settings'][ self::ITEMS ][ $key ];
            $expected['value']['key']['value'] = $string->get_value();
            if ( $item !== $expected ) {
                return [ null, null ];
            }
            unset( $item['_id'] );
            return [ $key, $item ];
        } catch ( Throwable $error ) {
            return [ null, null ];
        }
    }

    public function get_items_field() {
        return self::ITEMS;
    }

    public function get_field_path( $key ) {
        return self::handler()->get_field_path( $key );
    }
}
add_filter( 'wpml_elementor_widgets_to_translate', [ 'Netmilk_WPML_Select_Options', 'register' ], 100 );

/** Frontend-only fallback; never changes kit metadata, language or saved elements. */
class Netmilk_WPML_Global_Class_Labels {
    private static $running = false;

    private static function post_id( $value ) {
        if ( is_int( $value ) && $value > 0 ) {
            return $value;
        }
        if ( is_string( $value ) && preg_match( '/^[1-9][0-9]*$/D', $value )
            && (string) (int) $value === $value ) {
            return (int) $value;
        }
        return 0;
    }

    private static function global_id( $value ) {
        return is_string( $value ) && 1 === preg_match( '/^g-[a-zA-Z0-9_-]+$/D', $value );
    }

    public static function filter( $ids ) {
        if ( self::$running || ! is_array( $ids ) ) {
            return $ids;
        }
        $has_unresolved = false;
        foreach ( $ids as $id ) {
            if ( ! is_string( $id ) ) {
                return $ids;
            }
            $has_unresolved = $has_unresolved || self::global_id( $id );
        }
        if ( ! $has_unresolved ) {
            return $ids;
        }
        self::$running = true;
        try {
            return self::resolve( $ids );
        } catch ( Throwable $error ) {
            return $ids;
        } finally {
            self::$running = false;
        }
    }

    private static function resolve( $ids ) {
        $kit_class = '\\Elementor\\Core\\Kits\\Documents\\Kit';
        $repository = '\\Elementor\\Modules\\GlobalClasses\\Global_Classes_Repository';
        if ( ! class_exists( '\\Elementor\\Plugin' ) || ! class_exists( $kit_class )
            || ! is_callable( [ $repository, 'make' ] )
            || is_admin() || is_preview()
            || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return $ids;
        }
        $plugin = \Elementor\Plugin::$instance;
        if ( ! isset( $plugin->preview, $plugin->kits_manager )
            || ! is_callable( [ $plugin->preview, 'is_editor_or_preview' ] )
            || false !== $plugin->preview->is_editor_or_preview()
            || ! is_callable( [ $plugin->kits_manager, 'get_active_kit' ] ) ) {
            return $ids;
        }
        $base_id = self::post_id( get_option( 'elementor_active_kit' ) );
        $default_language = apply_filters( 'wpml_default_language', null );
        $language = apply_filters( 'wpml_current_language', null );
        if ( ! $base_id || ! is_string( $default_language ) || '' === $default_language
            || ! is_string( $language ) || '' === $language || 'all' === $language
            || 'all' === $default_language || $language === $default_language ) {
            return $ids;
        }
        $kit = $plugin->kits_manager->get_active_kit();
        if ( ! $kit instanceof $kit_class ) {
            return $ids;
        }
        $kit_id = self::post_id( $kit->get_id() );
        if ( ! $kit_id || $kit_id === $base_id
            || self::post_id( apply_filters( 'wpml_object_id', $kit_id, 'elementor_library', false, $default_language ) ) !== $base_id
            || self::post_id( apply_filters( 'wpml_object_id', $base_id, 'elementor_library', false, $language ) ) !== $kit_id ) {
            return $ids;
        }
        foreach ( [ $base_id, $kit_id ] as $post_id ) {
            if ( 'elementor_library' !== get_post_type( $post_id )
                || 'publish' !== get_post_status( $post_id )
                || 'kit' !== get_post_meta( $post_id, '_elementor_template_type', true ) ) {
                return $ids;
            }
        }
        // Protect even incomplete target declarations; they are not ours to replace.
        $target_labels = $kit->get_meta( '_elementor_global_classes_labels' );
        $target_posts = $kit->get_meta( '_elementor_global_classes_post_ids' );
        $target_repository = $repository::make( $kit )->set_preview( false );
        $target_order = $target_repository->get_order();
        if ( '' === $target_labels ) {
            $target_labels = [];
        }
        if ( '' === $target_posts ) {
            $target_posts = [];
        }
        if ( ! is_array( $target_labels ) || ! is_array( $target_order ) || ! is_array( $target_posts ) ) {
            return $ids;
        }
        foreach ( $target_labels as $id => $label ) {
            if ( ! self::global_id( $id ) || ! is_string( $label ) ) {
                return $ids;
            }
        }
        foreach ( $target_order as $id ) {
            if ( ! self::global_id( $id ) ) {
                return $ids;
            }
        }
        foreach ( $target_posts as $id => $post_id ) {
            if ( ! self::global_id( $id ) || ! self::post_id( $post_id ) ) {
                return $ids;
            }
        }
        // documents->get($base_id) can redirect to the translated kit in this context.
        $source = new $kit_class( [ 'post_id' => $base_id ] );
        if ( self::post_id( $source->get_id() ) !== $base_id ) {
            return $ids;
        }
        $labels = $repository::make( $source )->set_preview( false )->all_labels();
        if ( ! is_array( $labels ) ) {
            return $ids;
        }
        $counts = [];
        foreach ( $labels as $id => $label ) {
            if ( ! self::global_id( $id ) || ! is_string( $label ) ) {
                return $ids;
            }
            $counts[ $label ] = ( $counts[ $label ] ?? 0 ) + 1;
        }
        foreach ( $ids as $position => $id ) {
            if ( ! self::global_id( $id ) || ! isset( $labels[ $id ] )
                || array_key_exists( $id, $target_labels ) || in_array( $id, $target_order, true )
                || array_key_exists( $id, $target_posts )
                || in_array( $id, $target_labels, true ) ) {
                continue;
            }
            $label = $labels[ $id ];
            // Only unambiguous, single CSS identifiers. Unknown names remain native.
            if ( 1 !== $counts[ $label ] || self::global_id( $label )
                || 1 !== preg_match( '/^-?[_a-zA-Z][_a-zA-Z0-9-]*$/D', $label )
                || in_array( $label, $target_labels, true ) ) {
                continue;
            }
            $ids[ $position ] = $label;
        }
        return $ids;
    }
}

add_filter( 'elementor/atomic-widgets/settings/transformers/classes', [ 'Netmilk_WPML_Global_Class_Labels', 'filter' ], 100 );
