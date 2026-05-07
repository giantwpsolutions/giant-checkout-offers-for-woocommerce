<?php

namespace GiantCheckoutOffers;

if ( ! defined( 'ABSPATH' ) ) { exit; }

use GiantCheckoutOffers\Traits\SingletonTrait;

/**
 * Class Assets
 *
 * Manages the assets for the Giant Checkout Offers for WooCommerce plugin.
 */

class Assets {

    use SingletonTrait;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'gcow_enqueue_admin_assets' ], 50 );
        add_action( 'wp_enqueue_scripts', [ $this, 'gcow_enqueue_frontend_assets' ] );

        add_filter( 'script_loader_tag', [ $this, 'add_attribute_type' ], 10, 3 );
    }

    /**
     * Enqueue admin assets (Vue app)
     */
    public function gcow_enqueue_admin_assets() {
        if ( ! is_admin() ) return;

        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'woocommerce_page_giant-checkout-offers-for-woocommerce' ) return;

        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-api-fetch' );

            // Production: Load from dist folder
            $prod_js  = plugin_dir_url( __DIR__ ) . 'dist/assets/main.js';
            $prod_css = plugin_dir_url( __DIR__ ) . 'dist/assets/main.css';

            wp_enqueue_script(
                'gcow-vue-app',
                $prod_js,
                [ 'wp-i18n' ],
                time(),
                true
            );

            wp_enqueue_style(
                'gcow-vue-styles',
                $prod_css,
                [],
                time()
            );
        

        wp_localize_script(
            'gcow-vue-app',
            'gcowPluginData',
            [
                'pluginUrl'     => esc_url( plugin_dir_url( __DIR__ ) ),
                'restUrl'       => esc_url_raw( rest_url( trailingslashit( 'gcow/v2' ) ) ),
                'nonce'         => wp_create_nonce( 'wp_rest' ),
                'wcPlaceholder' => function_exists( 'wc_placeholder_img_src' ) ? esc_url( wc_placeholder_img_src() ) : '',
                'proActive'     => defined( 'GCOW_PRO_ACTIVE' ) && GCOW_PRO_ACTIVE,
            ]
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function gcow_enqueue_frontend_assets() {
        // Frontend assets will be added here as needed
    }

    /**
     * Add type="module" for Vite/Vue build in admin.
     */
    public function add_attribute_type( $tag, $handle, $src ) {
        if ( 'gcow-vue-app' === $handle ) {
            return str_replace(
                '<script ',
                '<script type="module" ',
                $tag
            );
        }
        return $tag;
    }

}