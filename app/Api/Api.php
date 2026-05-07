<?php
/**
 * API Handler for Giant Checkout Offers for WooCommerce.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api;

use GiantCheckoutOffers\Traits\SingletonTrait;
use GiantCheckoutOffers\Api\Controllers\Shared\Products_Controller;
use GiantCheckoutOffers\Api\Controllers\Shared\Categories_Controller;
use GiantCheckoutOffers\Api\Controllers\Shared\Tags_Controller;
use GiantCheckoutOffers\Api\Controllers\Settings\BumpData;
use GiantCheckoutOffers\Api\Controllers\Settings\SettingsData;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all REST API routes for the free plugin.
 * Analytics and AI Engine routes are registered by Giant Checkout Offers Pro.
 */
class Api {

	use SingletonTrait;

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );
	}

	public function register_api_routes() {
		( new Products_Controller() )->register_routes();
		( new Categories_Controller() )->register_routes();
		( new Tags_Controller() )->register_routes();
		( new BumpData() )->register_routes();
		( new SettingsData() )->register_routes();
	}
}
