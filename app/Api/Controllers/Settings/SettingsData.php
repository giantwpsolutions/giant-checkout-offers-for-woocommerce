<?php
/**
 * Settings REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Settings;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Handles get/update for plugin settings.
 */
class SettingsData extends WP_REST_Controller {

    /**
     * Option key for storing settings.
     */
    const OPTION_KEY = 'gcow_settings';

    /**
     * Default settings.
     */
    private static $defaults = [
        'selectedTemplate'    => 'standard',
        'templateStyles'      => [],
        'variationSwatches'   => true,
        'aiProductSuggestion' => false,
        'aiApiKeys'           => [ 'openai' => '', 'anthropic' => '', 'gemini' => '' ],
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'settings';
    }

    /**
     * Registers REST API routes.
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_settings' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'update_settings' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
    }

    /**
     * Permission callback.
     *
     * @param WP_REST_Request $request The request object.
     * @return bool|WP_Error
     */
    public function permission_callback( $request ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new WP_Error(
                'gcow_rest_forbidden',
                __( 'You do not have permission to access this resource.', 'giant-checkout-offers-for-woocommerce' ),
                [ 'status' => 403 ]
            );
        }

        $method = $request->get_method();
        if ( in_array( $method, [ 'POST', 'PUT', 'PATCH', 'DELETE' ], true ) ) {
            $nonce = $request->get_header( 'X-WP-Nonce' );
            if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
                return new WP_Error(
                    'gcow_rest_invalid_nonce',
                    __( 'Cookie nonce is invalid.', 'giant-checkout-offers-for-woocommerce' ),
                    [ 'status' => 403 ]
                );
            }
        }

        return true;
    }

    /**
     * Get all settings.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function get_settings( WP_REST_Request $request ) {
        $settings = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $settings ) ) {
            $settings = [];
        }

        $settings = wp_parse_args( $settings, self::$defaults );

        $settings['aiApiKeys'] = $this->mask_api_keys( $this->get_raw_keys() );

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $settings,
            ],
            200
        );
    }

    /**
     * Update settings.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function update_settings( WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params ) || ! is_array( $params ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Invalid or empty data provided.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                400
            );
        }

        $current  = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $current ) ) {
            $current = [];
        }

        // Sanitize incoming fields
        $sanitized = [];

        if ( isset( $params['selectedTemplate'] ) ) {
            $valid_templates = [ 'standard', 'compact', 'hero', 'ribbon', 'split', 'floating', 'timeline', 'social' ];
            $val = sanitize_text_field( $params['selectedTemplate'] );
            $sanitized['selectedTemplate'] = in_array( $val, $valid_templates, true ) ? $val : 'standard';
        }

        if ( isset( $params['templateStyles'] ) && is_array( $params['templateStyles'] ) ) {
            $sanitized['templateStyles'] = $this->sanitize_template_styles( $params['templateStyles'] );
        }

        if ( isset( $params['variationSwatches'] ) ) {
            $sanitized['variationSwatches'] = (bool) $params['variationSwatches'];
        }

        if ( isset( $params['aiProductSuggestion'] ) ) {
            $sanitized['aiProductSuggestion'] = (bool) $params['aiProductSuggestion'];
        }

        if ( isset( $params['aiApiKeys'] ) && is_array( $params['aiApiKeys'] ) ) {
            foreach ( [ 'openai', 'anthropic', 'gemini' ] as $provider ) {
                if ( ! array_key_exists( $provider, $params['aiApiKeys'] ) ) {
                    continue;
                }
                $val = trim( (string) $params['aiApiKeys'][ $provider ] );
                if ( strpos( $val, '•' ) !== false ) {
                    continue; // masked preview sent back — ignore
                }
                // Store each key in its own option — avoids any array-merge/cache issues.
                update_option( 'gcow_ai_key_' . $provider, $val );
            }
        }

        $updated = array_merge( $current, $sanitized );
        update_option( self::OPTION_KEY, $updated );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Settings saved successfully.', 'giant-checkout-offers-for-woocommerce' ),
                'data'    => $updated,
            ],
            200
        );
    }

    /**
     * Read raw API keys from their dedicated options.
     *
     * @return array [ 'openai' => '...', 'anthropic' => '...', 'gemini' => '...' ]
     */
    private function get_raw_keys() {
        return [
            'openai'    => (string) get_option( 'gcow_ai_key_openai',    '' ),
            'anthropic' => (string) get_option( 'gcow_ai_key_anthropic', '' ),
            'gemini'    => (string) get_option( 'gcow_ai_key_gemini',    '' ),
        ];
    }

    /**
     * Transform raw API keys into masked format for the frontend.
     *
     * @param array $raw_keys Keyed by provider: [ 'openai' => 'sk-proj-...', ... ]
     * @return array
     */
    private function mask_api_keys( $raw_keys ) {
        $providers = [ 'openai', 'anthropic', 'gemini' ];
        $masked    = [];

        foreach ( $providers as $provider ) {
            $key = $raw_keys[ $provider ] ?? '';

            if ( ! empty( $key ) ) {
                $masked[ $provider ] = [
                    'connected' => true,
                    'preview'   => $this->mask_key( $key ),
                ];
            } else {
                $masked[ $provider ] = [
                    'connected' => false,
                    'preview'   => '',
                ];
            }
        }

        return $masked;
    }

    /**
     * Mask an API key: show first 6 chars, bullets in middle, last 4 chars.
     *
     * @param string $key The raw API key.
     * @return string
     */
    private function mask_key( $key ) {
        $len = strlen( $key );

        if ( $len <= 10 ) {
            return str_repeat( '•', $len );
        }

        return substr( $key, 0, 6 ) . str_repeat( '•', max( 6, $len - 10 ) ) . substr( $key, -4 );
    }

    /**
     * Sanitize template styles.
     *
     * @param array $styles Keyed by template ID.
     * @return array
     */
    private function sanitize_template_styles( $styles ) {
        $valid_templates   = [ 'standard', 'compact', 'hero', 'ribbon', 'split', 'floating', 'timeline', 'social' ];
        $valid_border      = [ 'solid', 'dashed', 'none' ];
        $color_keys        = [
            'backgroundColor', 'borderColor', 'textColor', 'buttonColor',
            'descriptionColor', 'priceColor', 'buttonTextColor',
            'headerBgColor', 'headerTextColor', 'ctaBgColor', 'ctaBorderColor',
            'saveBadgeColor', 'ribbonColor', 'accentColor',
            'toggleColor', 'starColor', 'badgeColor', 'badgeTextColor',
        ];
        $number_keys       = [
            'borderWidth'  => [ 'min' => 0, 'max' => 5 ],
            'borderRadius' => [ 'min' => 0, 'max' => 24 ],
            'padding'      => [ 'min' => 4, 'max' => 32 ],
        ];
        $sanitized_styles  = [];

        foreach ( $styles as $template_id => $style_data ) {
            if ( ! in_array( $template_id, $valid_templates, true ) || ! is_array( $style_data ) ) {
                continue;
            }

            $clean = [];

            foreach ( $color_keys as $key ) {
                if ( isset( $style_data[ $key ] ) && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $style_data[ $key ] ) ) {
                    $clean[ $key ] = sanitize_hex_color( $style_data[ $key ] );
                }
            }

            foreach ( $number_keys as $key => $range ) {
                if ( isset( $style_data[ $key ] ) ) {
                    $val = (int) $style_data[ $key ];
                    $clean[ $key ] = max( $range['min'], min( $range['max'], $val ) );
                }
            }

            if ( isset( $style_data['borderStyle'] ) && in_array( $style_data['borderStyle'], $valid_border, true ) ) {
                $clean['borderStyle'] = $style_data['borderStyle'];
            }

            if ( ! empty( $clean ) ) {
                $sanitized_styles[ $template_id ] = $clean;
            }
        }

        return $sanitized_styles;
    }
}
