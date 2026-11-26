<?php
/**
 * AI Engine REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\AiEngine;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Handles AI Engine status, learning, and toggle.
 */
class AiEngineController extends WP_REST_Controller {

    const KNOWLEDGE_KEY = 'gcow_ai_knowledge';
    const BUMPS_KEY     = 'gcow_bumps';

    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'ai-engine';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/verify-key',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'verify_key' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/test-key',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'test_key' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/status',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_status' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/learn',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'learn' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/toggle',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'toggle' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );
    }

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
     * GET /ai-engine/test-key — diagnostic, returns key info + raw Anthropic response.
     */
    public function test_key( WP_REST_Request $request ) {
        $api_key = $this->get_anthropic_key();

        $key_info = [
            'stored'  => ! empty( $api_key ),
            'length'  => strlen( $api_key ),
            'preview' => strlen( $api_key ) > 10
                ? substr( $api_key, 0, 6 ) . '...' . substr( $api_key, -4 )
                : '(too short)',
            'first_char_ord' => ! empty( $api_key ) ? ord( $api_key[0] ) : null,
        ];

        if ( empty( $api_key ) ) {
            return new WP_REST_Response( [ 'key_info' => $key_info, 'anthropic' => null ], 200 );
        }

        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            [
                'timeout' => 15,
                'headers' => [
                    'x-api-key'         => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ],
                'body' => wp_json_encode( [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 5,
                    'messages'   => [ [ 'role' => 'user', 'content' => 'hi' ] ],
                ] ),
            ]
        );

        $raw_status = is_wp_error( $response ) ? 'wp_error' : wp_remote_retrieve_response_code( $response );
        $raw_body   = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response );

        return new WP_REST_Response(
            [
                'key_info'   => $key_info,
                'http_code'  => $raw_status,
                'body'       => json_decode( $raw_body, true ) ?? $raw_body,
            ],
            200
        );
    }

    /**
     * POST /ai-engine/verify-key — test a provider key before saving.
     */
    public function verify_key( WP_REST_Request $request ) {
        $params   = $request->get_json_params();
        $provider = sanitize_key( $params['provider'] ?? '' );
        $key      = trim( (string) ( $params['key'] ?? '' ) );

        if ( empty( $key ) ) {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'No key provided.' ], 400 );
        }

        if ( $provider === 'anthropic' ) {
            $result = $this->ping_anthropic( $key );
        } elseif ( $provider === 'openai' ) {
            $result = $this->ping_openai( $key );
        } elseif ( $provider === 'gemini' ) {
            $result = $this->ping_gemini( $key );
        } else {
            return new WP_REST_Response( [ 'success' => false, 'message' => 'Unknown provider.' ], 400 );
        }

        return new WP_REST_Response( $result, 200 );
    }

    private function ping_anthropic( $key ) {
        $res  = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'timeout' => 15,
            'headers' => [
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1,
                'messages'   => [ [ 'role' => 'user', 'content' => 'hi' ] ],
            ] ),
        ] );

        if ( is_wp_error( $res ) ) {
            return [ 'success' => false, 'message' => 'Connection failed: ' . $res->get_error_message() ];
        }

        $code = (int) wp_remote_retrieve_response_code( $res );
        $body = json_decode( wp_remote_retrieve_body( $res ), true );

        if ( $code === 200 ) {
            return [ 'success' => true, 'message' => 'Anthropic key is valid.' ];
        }

        $msg = $body['error']['message'] ?? "HTTP $code";
        return [ 'success' => false, 'message' => "Anthropic: $msg" ];
    }

    private function ping_openai( $key ) {
        $res = wp_remote_get( 'https://api.openai.com/v1/models', [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
        ] );

        if ( is_wp_error( $res ) ) {
            return [ 'success' => false, 'message' => 'Connection failed: ' . $res->get_error_message() ];
        }

        $code = (int) wp_remote_retrieve_response_code( $res );
        $body = json_decode( wp_remote_retrieve_body( $res ), true );

        if ( $code === 200 ) {
            return [ 'success' => true, 'message' => 'OpenAI key is valid.' ];
        }

        $msg = $body['error']['message'] ?? "HTTP $code";
        return [ 'success' => false, 'message' => "OpenAI: $msg" ];
    }

    private function ping_gemini( $key ) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode( $key );
        $res = wp_remote_get( $url, [ 'timeout' => 15 ] );

        if ( is_wp_error( $res ) ) {
            return [ 'success' => false, 'message' => 'Connection failed: ' . $res->get_error_message() ];
        }

        $code = (int) wp_remote_retrieve_response_code( $res );
        $body = json_decode( wp_remote_retrieve_body( $res ), true );

        if ( $code === 200 ) {
            return [ 'success' => true, 'message' => 'Gemini key is valid.' ];
        }

        $msg = $body['error']['message'] ?? "HTTP $code";
        return [ 'success' => false, 'message' => "Gemini: $msg" ];
    }

    /**
     * GET /ai-engine/status
     */
    public function get_status( WP_REST_Request $request ) {
        $knowledge = get_option( self::KNOWLEDGE_KEY, [] );

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => [
                    'enabled'   => ! empty( $knowledge['enabled'] ),
                    'knowledge' => [
                        'product_count' => (int) ( $knowledge['product_count'] ?? 0 ),
                        'bump_count'    => (int) ( $knowledge['bump_count'] ?? 0 ),
                        'learned_at'    => $knowledge['learned_at'] ?? null,
                        'summary'       => $knowledge['summary'] ?? '',
                    ],
                ],
            ],
            200
        );
    }

    /**
     * Read the Anthropic API key directly from MySQL, bypassing all caches.
     * Checks both the new dedicated option and the legacy nested location.
     *
     * @return string
     */
    private function get_anthropic_key() {
        global $wpdb;

        // New storage: dedicated option
        $key = trim( (string) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                'gcow_ai_key_anthropic'
            )
        ) );

        if ( ! empty( $key ) ) {
            return $key;
        }

        // Legacy storage: nested inside gcow_settings
        $raw      = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                'gcow_settings'
            )
        );
        $settings = maybe_unserialize( $raw );
        if ( is_array( $settings ) ) {
            $key = trim( (string) ( $settings['aiApiKeys']['anthropic'] ?? '' ) );
        }

        return $key;
    }

    /**
     * POST /ai-engine/learn
     */
    public function learn( WP_REST_Request $request ) {
        $api_key = trim( (string) get_option( 'gcow_ai_key_anthropic', '' ) );

        if ( empty( $api_key ) ) {
            $legacy   = get_option( 'gcow_settings', [] );
            $api_key  = trim( (string) ( $legacy['aiApiKeys']['anthropic'] ?? '' ) );
        }

        if ( empty( $api_key ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Anthropic API key not configured. Please add your Claude API key in AI API Keys section.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                400
            );
        }

        // Build product list
        $products      = wc_get_products( [ 'status' => 'publish', 'limit' => 200 ] );
        $product_list  = [];
        foreach ( $products as $product ) {
            $terms      = get_the_terms( $product->get_id(), 'product_cat' );
            $categories = is_array( $terms ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';
            $product_list[] = [
                'id'         => $product->get_id(),
                'name'       => $product->get_name(),
                'categories' => $categories,
                'type'       => $product->get_type(),
            ];
        }

        // Build active bump list
        $bumps_raw = get_option( self::BUMPS_KEY, [] );
        $bump_list = [];
        foreach ( (array) $bumps_raw as $bump ) {
            if ( empty( $bump['id'] ) || ( $bump['status'] ?? '' ) !== 'active' ) {
                continue;
            }
            $product_id   = $bump['product'] ?? $bump['productId'] ?? 0;
            $bump_product = $product_id ? wc_get_product( $product_id ) : null;
            $bump_list[]  = [
                'id'          => $bump['id'],
                'name'        => $bump_product ? $bump_product->get_name() : ( $bump['headline'] ?? $bump['id'] ),
                'headline'    => $bump['headline'] ?? '',
            ];
        }

        if ( empty( $bump_list ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'No active bumps found. Please create at least one active bump first.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                400
            );
        }

        if ( empty( $product_list ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'No published products found in your store.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                400
            );
        }

        // Call Claude API
        $prompt   = $this->build_prompt( $product_list, $bump_list );
        $response = $this->call_claude( $api_key, $prompt );

        if ( is_wp_error( $response ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => $response->get_error_message(),
                ],
                500
            );
        }

        $parsed      = $this->parse_response( $response );
        $mappings    = $parsed['mappings'] ?? [];
        $suggestions = $parsed['suggestions'] ?? [];
        $summary     = isset( $parsed['summary'] ) ? sanitize_text_field( $parsed['summary'] ) : '';

        // Sanitize suggestions: keys and values must be positive integers
        $clean_suggestions = [];
        foreach ( $suggestions as $pid => $related_ids ) {
            $pid = (string) absint( $pid );
            if ( ! $pid || ! is_array( $related_ids ) ) continue;
            $clean_suggestions[ $pid ] = array_values( array_filter( array_map( 'absint', $related_ids ) ) );
        }

        $existing  = get_option( self::KNOWLEDGE_KEY, [] );
        $knowledge = [
            'enabled'       => ! empty( $existing['enabled'] ),
            'product_count' => count( $product_list ),
            'bump_count'    => count( $bump_list ),
            'learned_at'    => current_time( 'mysql' ),
            'summary'       => $summary,
            'mappings'      => $mappings,
            'suggestions'   => $clean_suggestions,
        ];

        update_option( self::KNOWLEDGE_KEY, $knowledge );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => sprintf(
                    /* translators: %1$d products, %2$d bumps */
                    __( 'Analysis complete — %1$d products mapped across %2$d bumps.', 'giant-checkout-offers-for-woocommerce' ),
                    count( $product_list ),
                    count( $bump_list )
                ),
                'data'    => [
                    'product_count' => $knowledge['product_count'],
                    'bump_count'    => $knowledge['bump_count'],
                    'learned_at'    => $knowledge['learned_at'],
                    'summary'       => $knowledge['summary'],
                ],
            ],
            200
        );
    }

    /**
     * POST /ai-engine/toggle
     */
    public function toggle( WP_REST_Request $request ) {
        $params    = $request->get_json_params();
        $enabled   = ! empty( $params['enabled'] );
        $knowledge = get_option( self::KNOWLEDGE_KEY, [] );

        $knowledge['enabled'] = $enabled;
        update_option( self::KNOWLEDGE_KEY, $knowledge );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => $enabled
                    ? __( 'AI Engine enabled.', 'giant-checkout-offers-for-woocommerce' )
                    : __( 'AI Engine disabled.', 'giant-checkout-offers-for-woocommerce' ),
            ],
            200
        );
    }

    /**
     * Build the Claude prompt.
     */
    private function build_prompt( $products, $bumps ) {
        $product_json = wp_json_encode( $products );
        $bumps_json   = wp_json_encode( $bumps );

        return "You are a WooCommerce expert helping an online store maximize revenue.\n\n" .
            "Given these published products and active order bump offers, do two things:\n\n" .
            "1. BUMP MAPPING: For each product, assign the most relevant order bump.\n" .
            "2. PRODUCT SUGGESTIONS: For each product, list up to 5 other product IDs from the catalog that a customer viewing that product would also find interesting (complementary items, accessories, same category, similar use-case). Use only IDs that exist in the PRODUCTS list.\n\n" .
            "PRODUCTS:\n{$product_json}\n\n" .
            "BUMPS:\n{$bumps_json}\n\n" .
            "Return ONLY a valid JSON object in exactly this format — no explanation, no markdown, no code fences:\n" .
            '{"summary":"One sentence describing your overall strategy","mappings":{"<product_id>":"<bump_id>"},"suggestions":{"<product_id>":[<related_product_id>,<related_product_id>]}}' . "\n\n" .
            "Rules:\n" .
            "- mappings: every product must have exactly one bump assigned (numeric product ID as string key)\n" .
            "- mappings: if only one bump exists, assign it to every product\n" .
            "- suggestions: numeric product ID as string key, value is an array of up to 5 numeric product IDs\n" .
            "- suggestions: never include the product itself in its own suggestion list\n" .
            "- Return only the raw JSON — nothing else";
    }

    /**
     * Call Anthropic Claude API.
     *
     * @return array|WP_Error
     */
    private function call_claude( $api_key, $prompt ) {
        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            [
                'timeout' => 90,
                'headers' => [
                    'x-api-key'         => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type'      => 'application/json',
                ],
                'body'    => wp_json_encode(
                    [
                        'model'      => 'claude-haiku-4-5-20251001',
                        'max_tokens' => 4096,
                        'messages'   => [
                            [ 'role' => 'user', 'content' => $prompt ],
                        ],
                    ]
                ),
            ]
        );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'gcow_ai_request_failed',
                sprintf(
                    __( 'Could not reach Claude API: %s', 'giant-checkout-offers-for-woocommerce' ),
                    $response->get_error_message()
                )
            );
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $error   = $body['error']['message'] ?? "Unexpected response (HTTP $code)";
            $len     = strlen( $api_key );
            $preview = $len > 10 ? substr( $api_key, 0, 8 ) . '...' . substr( $api_key, -4 ) . " ({$len} chars)" : "(too short: {$len} chars)";
            return new WP_Error( 'gcow_ai_api_error', "$error [stored key: $preview]" );
        }

        return $body;
    }

    /**
     * Parse Claude's JSON response.
     *
     * @param array $response Raw API response body.
     * @return array { mappings: array, summary: string }
     */
    private function parse_response( $response ) {
        $text = $response['content'][0]['text'] ?? '';

        // Strip markdown code fences if Claude wrapped the JSON
        $text = preg_replace( '/^```(?:json)?\s*/i', '', trim( $text ) );
        $text = preg_replace( '/\s*```$/', '', $text );

        $parsed = json_decode( trim( $text ), true );

        if ( json_last_error() === JSON_ERROR_NONE && isset( $parsed['mappings'] ) ) {
            return $parsed;
        }

        // Fallback: extract first JSON object from the text
        if ( preg_match( '/\{[\s\S]*\}/m', $text, $matches ) ) {
            $parsed = json_decode( $matches[0], true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                return $parsed;
            }
        }

        return [ 'mappings' => [], 'suggestions' => [], 'summary' => $text ];
    }
}
