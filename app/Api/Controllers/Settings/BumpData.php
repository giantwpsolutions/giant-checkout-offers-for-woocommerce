<?php
/**
 * Bump Data REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Settings;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use GiantCheckoutOffers\Helper\Sanitization\BumpDataSanitization;

defined( 'ABSPATH' ) || exit;

/**
 * Handles CRUD operations for order bumps.
 */
class BumpData extends WP_REST_Controller {

    /**
     * Option key for storing bumps in the database.
     */
    const OPTION_KEY = 'gcow_bumps';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'bumps';
    }

    /**
     * Registers REST API routes for bump CRUD.
     *
     * @return void
     */
    public function register_routes() {

        // GET all bumps / POST create bump
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_bumps' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_bump' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );

        // GET / PUT / DELETE single bump
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\w.-]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_bump' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_bump' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_bump' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );

        // PATCH toggle bump status
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\w.-]+)/toggle',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'toggle_bump_status' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );
    }

    /**
     * Permission callback for all bump endpoints.
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

        // Verify nonce for write operations
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
     * Get all bumps.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response
     */
    public function get_bumps( WP_REST_Request $request ) {
        $bumps = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $bumps ) ) {
            $bumps = [];
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => array_values( $bumps ),
            ],
            200
        );
    }

    /**
     * Get a single bump by ID.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_bump( WP_REST_Request $request ) {
        $id    = sanitize_text_field( $request->get_param( 'id' ) );
        $bumps = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $bumps ) || ! isset( $bumps[ $id ] ) ) {
            return new WP_Error(
                'gcow_bump_not_found',
                __( 'Bump not found.', 'giant-checkout-offers-for-woocommerce' ),
                [ 'status' => 404 ]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => $bumps[ $id ],
            ],
            200
        );
    }

    /**
     * Create a new bump.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function create_bump( WP_REST_Request $request ) {
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

        $sanitized = BumpDataSanitization::sanitize( $params );

        if ( empty( $sanitized ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Data sanitization failed.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                400
            );
        }

        // Ensure unique ID
        $sanitized['id']        = uniqid( 'bump_', true );
        $sanitized['createdAt'] = current_time( 'c' );

        $bumps = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $bumps ) ) {
            $bumps = [];
        }

        $bumps[ $sanitized['id'] ] = $sanitized;

        $saved = update_option( self::OPTION_KEY, $bumps );

        if ( ! $saved && get_option( self::OPTION_KEY ) === false ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Failed to save bump.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                500
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Bump created successfully.', 'giant-checkout-offers-for-woocommerce' ),
                'data'    => $sanitized,
            ],
            200
        );
    }

    /**
     * Update an existing bump.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function update_bump( WP_REST_Request $request ) {
        $id     = sanitize_text_field( $request->get_param( 'id' ) );
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

        $bumps = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $bumps ) || ! isset( $bumps[ $id ] ) ) {
            return new WP_Error(
                'gcow_bump_not_found',
                __( 'Bump not found.', 'giant-checkout-offers-for-woocommerce' ),
                [ 'status' => 404 ]
            );
        }

        // Preserve original ID and createdAt
        $params['id']        = $id;
        $params['createdAt'] = $bumps[ $id ]['createdAt'];

        $sanitized = BumpDataSanitization::sanitize( $params );

        $bumps[ $id ] = array_merge( $bumps[ $id ], $sanitized );

        $saved = update_option( self::OPTION_KEY, $bumps );

        if ( ! $saved && get_option( self::OPTION_KEY ) === false ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Failed to update bump.', 'giant-checkout-offers-for-woocommerce' ),
                ],
                500
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Bump updated successfully.', 'giant-checkout-offers-for-woocommerce' ),
                'data'    => $bumps[ $id ],
            ],
            200
        );
    }

    /**
     * Delete a bump.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function delete_bump( WP_REST_Request $request ) {
        $id    = sanitize_text_field( $request->get_param( 'id' ) );
        $bumps = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $bumps ) || ! isset( $bumps[ $id ] ) ) {
            return new WP_Error(
                'gcow_bump_not_found',
                __( 'Bump not found.', 'giant-checkout-offers-for-woocommerce' ),
                [ 'status' => 404 ]
            );
        }

        unset( $bumps[ $id ] );

        update_option( self::OPTION_KEY, $bumps );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Bump deleted successfully.', 'giant-checkout-offers-for-woocommerce' ),
            ],
            200
        );
    }

    /**
     * Toggle bump status (active/inactive).
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function toggle_bump_status( WP_REST_Request $request ) {
        $id    = sanitize_text_field( $request->get_param( 'id' ) );
        $bumps = get_option( self::OPTION_KEY, [] );

        if ( ! is_array( $bumps ) || ! isset( $bumps[ $id ] ) ) {
            return new WP_Error(
                'gcow_bump_not_found',
                __( 'Bump not found.', 'giant-checkout-offers-for-woocommerce' ),
                [ 'status' => 404 ]
            );
        }

        $current_status = $bumps[ $id ]['status'] ?? 'active';
        $bumps[ $id ]['status']    = ( $current_status === 'active' ) ? 'inactive' : 'active';
        $bumps[ $id ]['updatedAt'] = current_time( 'c' );

        update_option( self::OPTION_KEY, $bumps );

        return new WP_REST_Response(
            [
                'success' => true,
                'message' => __( 'Bump status updated.', 'giant-checkout-offers-for-woocommerce' ),
                'data'    => $bumps[ $id ],
            ],
            200
        );
    }
}
