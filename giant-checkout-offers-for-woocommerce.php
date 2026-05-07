<?php
/**
 * Plugin Name: Giant Checkout Offers for WooCommerce
 * Plugin URI: https://www.giantwpsolutions.com/giant-checkout-offers-for-woocommerce/
 * Description: Boost your WooCommerce sales with beautiful checkout offers. Show irresistible order bumps at checkout to increase average order value and revenue.
 * Version: 1.0.0
 * Author: Giant WP Solutions
 * Author URI: https://giantwpsolutions.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: giant-checkout-offers-for-woocommerce
 * Requires at least: 5.8
 * Tested up to: 6.9
 * WC requires at least: 3.0.0
 * WC Tested up to: 10.4.3
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WooCommerce HPOS support: yes
 * Domain path: /languages
 * @package GiantCheckoutOffersForWooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/functions.php';

/**
 * The main plugin class.
 */
final class Giant_Checkout_Offers {

    const version = '1.0.0';

    public function __construct() {
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
        add_action( 'admin_notices', [ $this, 'check_woocommerce_active' ] );
        $this->declare_hpos_compatibility();
        $this->define_constants();
    }

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    public function define_constants() {
        defined( 'GCOW_VERSION' )     || define( 'GCOW_VERSION', self::version );
        defined( 'GCOW_FILE' )        || define( 'GCOW_FILE', __FILE__ );
        defined( 'GCOW_PATH' )        || define( 'GCOW_PATH', __DIR__ . '/' );
        defined( 'GCOW_URL' )         || define( 'GCOW_URL', plugins_url( '', __FILE__ ) );
        defined( 'GCOW_ASSETS' )      || define( 'GCOW_ASSETS', GCOW_URL . '/assets' );
        defined( 'GCOW_PLUGIN_PATH' ) || define( 'GCOW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        defined( 'GCOW_PLUGIN_URL' )  || define( 'GCOW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    public function activate() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );

            wp_die(
                esc_html__( 'Giant Checkout Offers requires WooCommerce to be installed and active.', 'giant-checkout-offers-for-woocommerce' ),
                esc_html__( 'Plugin dependency check', 'giant-checkout-offers-for-woocommerce' ),
                [ 'back_link' => true ]
            );
        }

        $install_time = get_option( 'gcow_installation_time' );

        if ( ! $install_time ) {
            update_option( 'gcow_installation_time', time() );
        }

        update_option( 'gcow_version', self::version );
    }

    public function on_plugins_loaded() {
        if ( class_exists( 'WooCommerce' ) ) {
            \GiantCheckoutOffers\Api\Api::instance();
            \GiantCheckoutOffers\Init::instance();
        }
    }

    public function check_woocommerce_active() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            gcow_WoocommerceDeactivationAlert();
        }
    }

    public function declare_hpos_compatibility() {
        add_action( 'before_woocommerce_init', function () {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
            }
        } );
    }
}

/**
 * Returns the main plugin instance.
 *
 * @return Giant_Checkout_Offers
 */
function giantCheckoutOffers() {
    return Giant_Checkout_Offers::init();
}

giantCheckoutOffers();
