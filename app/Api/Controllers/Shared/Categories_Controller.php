<?php
/**
 * Product Categories REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Shared;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Class Categories_Controller
 *
 * Retrieves WooCommerce product categories.
 */
class Categories_Controller extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'categories';
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
                    'callback'            => [ $this, 'get_categories' ],
                    'permission_callback' => [ $this, 'get_categories_permission' ],
                    'args'                => [
                        'hierarchical' => [
                            'default'     => true,
                            'type'        => 'boolean',
                            'description' => 'Return categories in hierarchical format',
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * Checks if a given request has access to read categories.
     *
     * @return bool
     */
    public function get_categories_permission() {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Retrieves list of categories.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_categories( $request ) {
        $hierarchical = $request->get_param( 'hierarchical' );

        $args = [
            'taxonomy'     => 'product_cat',
            'hide_empty'   => false,
            'hierarchical' => true,
        ];

        $categories = get_terms( $args );

        if ( is_wp_error( $categories ) ) {
            return rest_ensure_response( $categories );
        }

        if ( $hierarchical ) {
            $data = $this->format_categories_hierarchical( $categories );
        } else {
            $data = $this->format_categories_flat( $categories );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Recursively formats categories into a flat list with labels.
     *
     * @param array  $categories List of term objects.
     * @param int    $parent_id  Parent category ID.
     * @param string $prefix     Prefix for child labels.
     * @return array
     */
    private function format_categories_hierarchical( $categories, $parent_id = 0, $prefix = '' ) {
        $output = [];

        foreach ( $categories as $category ) {
            if ( $category->parent == $parent_id ) {
                $formatted_category = [
                    'id'    => $category->term_id,
                    'name'  => $prefix . $category->name,
                    'slug'  => $category->slug,
                    'count' => $category->count,
                ];

                $children = $this->format_categories_hierarchical( $categories, $category->term_id, $prefix . $category->name . ' ⇒ ' );
                $output[] = $formatted_category;

                if ( ! empty( $children ) ) {
                    $output = array_merge( $output, $children );
                }
            }
        }

        return $output;
    }

    /**
     * Format categories as flat list.
     *
     * @param array $categories
     * @return array
     */
    private function format_categories_flat( $categories ) {
        return array_map( function ( $category ) {
            return [
                'id'        => $category->term_id,
                'name'      => $category->name,
                'slug'      => $category->slug,
                'parent'    => $category->parent,
                'count'     => $category->count,
                'thumbnail' => $this->get_category_thumbnail( $category->term_id ),
            ];
        }, $categories );
    }

    /**
     * Get category thumbnail.
     *
     * @param int $category_id
     * @return array|null
     */
    private function get_category_thumbnail( $category_id ) {
        $thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );

        if ( ! $thumbnail_id ) {
            return null;
        }

        return [
            'id'  => $thumbnail_id,
            'src' => wp_get_attachment_url( $thumbnail_id ),
        ];
    }
}
