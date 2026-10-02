<?php
/**
 * Plugin Name: Netmilk — WPML Elementor Component Fix
 * Description: Makes Elementor V4 escaped-html component overrides translatable through native WPML export/import. Automatically defers to a working native handler.
 * Version: 1.0.1
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
            $type = $parameter->hasType() ? (string) $parameter->getType() : '';
            if ( $type !== $expected_type || $parameter->isPassedByReference() || $parameter->isVariadic() ) {
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
