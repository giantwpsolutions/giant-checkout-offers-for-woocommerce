<?php

namespace GiantCheckoutOffers;

use GiantCheckoutOffers\Traits\SingletonTrait;
use GiantCheckoutOffers\Admin\AdminMenu;
use GiantCheckoutOffers\Analytics\Analytics;
use GiantCheckoutOffers\Frontend\Frontend;
use GiantCheckoutOffers\Frontend\PostPurchase;
use GiantCheckoutOffers\Frontend\ProductSuggestion;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Init
 *
 * Initializes the Giant Checkout Offers for WooCommerce plugin.
 */
class Init {

    use SingletonTrait;

    public function __construct() {
        Assets::instance();
        AdminMenu::instance();
        Analytics::instance();
        Frontend::instance();
        PostPurchase::instance();
        ProductSuggestion::instance();
    }

}
