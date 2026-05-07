<?php
/**
 * Product List REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Shared;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Class Products_Controller
 *
 * Retrieves a list of WooCommerce products with full details.
 */
class Products_Controller extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'products';
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
                    'callback'            => [ $this, 'get_products' ],
                    'permission_callback' => [ $this, 'get_products_permission' ],
                    'args'                => $this->get_collection_params(),
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_product' ],
                    'permission_callback' => [ $this, 'get_products_permission' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/search',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'search_products' ],
                    'permission_callback' => [ $this, 'get_products_permission' ],
                    'args'                => [
                        'search' => [
                            'required'    => true,
                            'type'        => 'string',
                            'description' => 'Search term for products',
                        ],
                        'limit' => [
                            'default'     => 20,
                            'type'        => 'integer',
                            'description' => 'Maximum number of results',
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * Get collection parameters.
     *
     * @return array
     */
    public function get_collection_params() {
        return [
            'page' => [
                'default'     => 1,
                'type'        => 'integer',
                'description' => 'Current page of the collection',
            ],
            'per_page' => [
                'default'     => 20,
                'type'        => 'integer',
                'description' => 'Maximum number of items to return per page',
            ],
            'search' => [
                'default'     => '',
                'type'        => 'string',
                'description' => 'Search term',
            ],
            'category' => [
                'default'     => '',
                'type'        => 'string',
                'description' => 'Category slug to filter',
            ],
            'include_variations' => [
                'default'     => true,
                'type'        => 'boolean',
                'description' => 'Include product variations',
            ],
        ];
    }

    /**
     * Checks if a given request has access to read products.
     *
     * @return bool
     */
    public function get_products_permission() {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Retrieves list of Products.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_products( $request ) {
        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $category = $request->get_param( 'category' );
        $include_variations = $request->get_param( 'include_variations' );

        $args = [
            'status'  => 'publish',
            'limit'   => $per_page,
            'page'    => $page,
            'orderby' => 'title',
            'order'   => 'ASC',
        ];

        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        if ( ! empty( $category ) ) {
            $args['category'] = [ $category ];
        }

        $products = wc_get_products( $args );

        $data = [];
        foreach ( $products as $product ) {
            $data[] = $this->format_product( $product, $include_variations );
        }

        return rest_ensure_response( [
            'products' => $data,
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => $this->get_total_products( $args ),
        ] );
    }

    /**
     * Retrieves a single product.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_product( $request ) {
        $product_id = $request->get_param( 'id' );
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            return new \WP_Error( 'product_not_found', __( 'Product not found.', 'giant-checkout-offers-for-woocommerce' ), [ 'status' => 404 ] );
        }

        return rest_ensure_response( $this->format_product( $product, true ) );
    }

    /**
     * Search products by term.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function search_products( $request ) {
        $search = $request->get_param( 'search' );
        $limit  = $request->get_param( 'limit' );

        $args = [
            'status' => 'publish',
            'limit'  => $limit,
            's'      => $search,
        ];

        $products = wc_get_products( $args );

        $data = [];
        foreach ( $products as $product ) {
            $data[] = $this->format_product_minimal( $product );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Format product data with full details.
     *
     * @param \WC_Product $product
     * @param bool        $include_variations
     * @return array
     */
    private function format_product( $product, $include_variations = true ) {
        $data = [
            'id'                => $product->get_id(),
            'name'              => $product->get_name(),
            'slug'              => $product->get_slug(),
            'type'              => $product->get_type(),
            'status'            => $product->get_status(),
            'sku'               => $product->get_sku(),
            'price'             => $product->get_price(),
            'regular_price'     => $product->get_regular_price(),
            'sale_price'        => $product->get_sale_price(),
            'price_html'        => $product->get_price_html(),
            'on_sale'           => $product->is_on_sale(),
            'purchasable'       => $product->is_purchasable(),
            'total_sales'       => $product->get_total_sales(),
            'virtual'           => $product->is_virtual(),
            'downloadable'      => $product->is_downloadable(),
            'short_description' => $product->get_short_description(),
            'description'       => $product->get_description(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'stock_status'      => $product->get_stock_status(),
            'manage_stock'      => $product->get_manage_stock(),
            'weight'            => $product->get_weight(),
            'dimensions'        => [
                'length' => $product->get_length(),
                'width'  => $product->get_width(),
                'height' => $product->get_height(),
            ],
            'image'             => $this->get_product_image( $product ),
            'gallery_images'    => $this->get_gallery_images( $product ),
            'categories'        => $this->get_product_categories( $product ),
            'tags'              => $this->get_product_tags( $product ),
            'attributes'        => $this->get_product_attributes( $product ),
            'meta_data'         => $this->get_product_meta( $product ),
        ];

        // Handle variable products
        if ( $include_variations && $product->is_type( 'variable' ) ) {
            $data['variations'] = $this->get_product_variations( $product );
        }

        return $data;
    }

    /**
     * Format product with minimal data (for search results).
     *
     * @param \WC_Product $product
     * @return array
     */
    private function format_product_minimal( $product ) {
        return [
            'id'          => $product->get_id(),
            'name'        => $product->get_name(),
            'type'        => $product->get_type(),
            'price'       => $product->get_price(),
            'price_html'  => $product->get_price_html(),
            'sku'         => $product->get_sku(),
            'image'       => $this->get_product_image( $product ),
            'stock_status' => $product->get_stock_status(),
        ];
    }

    /**
     * Get product main image.
     *
     * @param \WC_Product $product
     * @return array|null
     */
    private function get_product_image( $product ) {
        $image_id = $product->get_image_id();

        if ( ! $image_id ) {
            return null;
        }

        return [
            'id'        => $image_id,
            'src'       => wp_get_attachment_url( $image_id ),
            'thumbnail' => wp_get_attachment_image_url( $image_id, 'thumbnail' ),
            'alt'       => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
        ];
    }

    /**
     * Get product gallery images.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_gallery_images( $product ) {
        $gallery_ids = $product->get_gallery_image_ids();
        $images      = [];

        foreach ( $gallery_ids as $image_id ) {
            $images[] = [
                'id'        => $image_id,
                'src'       => wp_get_attachment_url( $image_id ),
                'thumbnail' => wp_get_attachment_image_url( $image_id, 'thumbnail' ),
                'alt'       => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
            ];
        }

        return $images;
    }

    /**
     * Get product categories.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_product_categories( $product ) {
        $category_ids = $product->get_category_ids();
        $categories   = [];

        foreach ( $category_ids as $cat_id ) {
            $term = get_term( $cat_id, 'product_cat' );
            if ( $term && ! is_wp_error( $term ) ) {
                $categories[] = [
                    'id'   => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }
        }

        return $categories;
    }

    /**
     * Get product tags.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_product_tags( $product ) {
        $tag_ids = $product->get_tag_ids();
        $tags    = [];

        foreach ( $tag_ids as $tag_id ) {
            $term = get_term( $tag_id, 'product_tag' );
            if ( $term && ! is_wp_error( $term ) ) {
                $tags[] = [
                    'id'   => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }
        }

        return $tags;
    }

    /**
     * Get product attributes.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_product_attributes( $product ) {
        $attributes     = $product->get_attributes();
        $formatted_atts = [];

        foreach ( $attributes as $attr ) {
            if ( is_a( $attr, 'WC_Product_Attribute' ) ) {
                $formatted_atts[] = [
                    'id'        => $attr->get_id(),
                    'name'      => $attr->get_name(),
                    'position'  => $attr->get_position(),
                    'visible'   => $attr->get_visible(),
                    'variation' => $attr->get_variation(),
                    'options'   => $attr->get_options(),
                ];
            }
        }

        return $formatted_atts;
    }

    /**
     * Get product meta data.
     *
     * @param \WC_Product $product
     * @return array
     */
    private function get_product_meta( $product ) {
        $meta_data = $product->get_meta_data();
        $formatted = [];

        foreach ( $meta_data as $meta ) {
            // Skip internal meta keys
            if ( strpos( $meta->key, '_' ) === 0 ) {
                continue;
            }

            $formatted[] = [
                'key'   => $meta->key,
                'value' => $meta->value,
            ];
        }

        return $formatted;
    }

    /**
     * Get product variations, expanding "Any" attributes into all possible combinations.
     *
     * When a variation has an attribute set to "" (Any), it is multiplied by every
     * available term of that attribute so the admin can select a specific combination.
     *
     * @param \WC_Product_Variable $product
     * @return array
     */
    private function get_product_variations( $product ) {
        $variation_ids       = $product->get_children();
        $product_attr_terms  = $product->get_variation_attributes(); // ['pa_color' => ['black','white'], ...]
        $variations          = [];

        foreach ( $variation_ids as $variation_id ) {
            $variation = wc_get_product( $variation_id );
            if ( ! $variation ) {
                continue;
            }

            $base = [
                'id'            => $variation->get_id(),
                'name'          => $variation->get_name(),
                'sku'           => $variation->get_sku(),
                'price'         => $variation->get_price(),
                'regular_price' => $variation->get_regular_price(),
                'sale_price'    => $variation->get_sale_price(),
                'price_html'    => $variation->get_price_html(),
                'on_sale'       => $variation->is_on_sale(),
                'purchasable'   => $variation->is_purchasable(),
                'stock_quantity' => $variation->get_stock_quantity(),
                'stock_status'  => $variation->get_stock_status(),
                'manage_stock'  => $variation->get_manage_stock(),
                'description'   => $variation->get_description(),
                'image'         => $this->get_product_image( $variation ),
                'weight'        => $variation->get_weight(),
                'dimensions'    => [
                    'length' => $variation->get_length(),
                    'width'  => $variation->get_width(),
                    'height' => $variation->get_height(),
                ],
            ];

            $raw_attrs = $variation->get_attributes(); // ['pa_color' => 'black', 'pa_size' => '']
            $combos    = $this->expand_any_attributes( $raw_attrs, $product_attr_terms );

            foreach ( $combos as $specific_attrs ) {
                $formatted_attrs = [];
                foreach ( $specific_attrs as $key => $value ) {
                    $formatted_attrs[] = [
                        'key'   => $key,
                        'label' => wc_attribute_label( $key ),
                        'value' => $value,
                    ];
                }

                // specific_attrs for add_to_cart needs the attribute_ prefix
                $cart_attrs = [];
                foreach ( $specific_attrs as $key => $value ) {
                    $cart_attrs[ 'attribute_' . $key ] = $value;
                }

                $variations[] = array_merge( $base, [
                    'attributes'     => $formatted_attrs,
                    'specific_attrs' => $cart_attrs,
                ] );
            }
        }

        return $variations;
    }

    /**
     * Expand "Any" (empty-string) attributes into all possible combinations.
     *
     * @param array $variation_attrs  Attributes from get_attributes(): ['pa_color' => 'black', 'pa_size' => '']
     * @param array $product_terms    All terms from product: ['pa_color' => ['black','white'], 'pa_size' => ['s','m','l']]
     * @return array  Array of specific attribute sets, e.g. [['pa_color'=>'black','pa_size'=>'s'], ...]
     */
    private function expand_any_attributes( array $variation_attrs, array $product_terms ) {
        $fixed      = [];
        $any_opts   = [];

        foreach ( $variation_attrs as $key => $value ) {
            if ( $value !== '' ) {
                $fixed[ $key ] = $value;
            } else {
                $any_opts[ $key ] = $product_terms[ $key ] ?? [];
            }
        }

        if ( empty( $any_opts ) ) {
            return [ $fixed ];
        }

        // Cartesian product of all "Any" attribute options
        $combos = [ [] ];
        foreach ( $any_opts as $key => $options ) {
            $expanded = [];
            foreach ( $combos as $combo ) {
                foreach ( $options as $opt ) {
                    $expanded[] = array_merge( $combo, [ $key => $opt ] );
                }
            }
            $combos = $expanded;
        }

        return array_map( fn( $c ) => array_merge( $fixed, $c ), $combos );
    }

    /**
     * Get total product count for pagination.
     *
     * @param array $args
     * @return int
     */
    private function get_total_products( $args ) {
        $args['limit']  = -1;
        $args['return'] = 'ids';
        unset( $args['page'] );

        return count( wc_get_products( $args ) );
    }
}
