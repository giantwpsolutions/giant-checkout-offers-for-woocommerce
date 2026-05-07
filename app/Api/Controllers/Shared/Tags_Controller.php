<?php
/**
 * Product Tags REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Shared;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Class Tags_Controller
 *
 * Retrieves WooCommerce product tags.
 */
class Tags_Controller extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'tags';
    }

    /**
     * Registers the routes for the objects of the controller.
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
                    'callback'            => [ $this, 'get_tags' ],
                    'permission_callback' => [ $this, 'get_tags_permission' ],
                ]
            ]
        );
    }

    /**
     * Checks if a given request has access to read tags.
     *
     * @return bool
     */
    public function get_tags_permission() {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Retrieves list of tags.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_tags( $request ) {
        $args = [
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
        ];

        $tags = get_terms( $args );

        if ( is_wp_error( $tags ) ) {
            return rest_ensure_response( $tags );
        }

        $data = array_map( function ( $tag ) {
            return [
                'id'    => $tag->term_id,
                'name'  => $tag->name,
                'slug'  => $tag->slug,
                'count' => $tag->count,
            ];
        }, $tags );

        return rest_ensure_response( $data );
    }
}
