<?php

namespace GiantCheckoutOffers;

use GiantCheckoutOffers\Traits\SingletonTrait;
use GiantCheckoutOffers\Admin\AdminMenu;
use GiantCheckoutOffers\Api\Api;
use GiantCheckoutOffers\Frontend\Frontend;

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
        Api::instance();
        Frontend::instance();
    }

}
