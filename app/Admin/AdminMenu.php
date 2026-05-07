<?php
/**
 * Admin Menu Setup for Giant Checkout Offers for WooCommerce.
 *
 * @package GiantCheckoutOffersForWooCommerce
 */

namespace GiantCheckoutOffers\Admin;

use GiantCheckoutOffers\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;



/**
 * Admin Menu Class
 */
class AdminMenu {

    use SingletonTrait;
    /**
     * Class Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', [ $this, 'gcow_menu' ] );
    }

    /**
     * Registers the Giant Checkout Offers submenu under WooCommerce.
     */
    public function gcow_menu() {

        global $submenu;
        // Check if WooCommerce is active
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;  // WooCommerce is not active, don't add the menu
        }

        // Add submenu under WooCommerce Marketing menu

        $parent_slug = 'woocommerce';
        $capability  = 'manage_woocommerce';

        add_submenu_page( $parent_slug, __( 'Giant Checkout Offers', 'giant-checkout-offers-for-woocommerce' ), __( 'Giant Checkout Offers', 'giant-checkout-offers-for-woocommerce' ), $capability, 'giant-checkout-offers-for-woocommerce', [ $this, 'render_page' ] );

    }

    /**
     * Render the submenu page content
     * 
     * @return void
     */
    public function render_page() {

        echo '<div class="wrap"><div id="giant-checkout-offers"></div></div>';
    }
}
