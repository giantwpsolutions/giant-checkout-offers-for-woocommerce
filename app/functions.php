<?php
defined( 'ABSPATH' ) || exit;

function gcow_WoocommerceDeactivationAlert()
{
?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php esc_html_e(
                'WooCommerce is deactivated! The "Giant Checkout Offers for WooCommerce" plugin requires WooCommerce to function properly. Please reactivate WooCommerce.',
                'giant-checkout-offers-for-woocommerce'
            ); ?>
        </p>
    </div>
<?php
}