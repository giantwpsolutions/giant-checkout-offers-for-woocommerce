<?php
/**
 * Analytics Tracker - Tracks bump performance metrics.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Analytics;

use GiantCheckoutOffers\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Handles analytics tracking for order bumps.
 */
class Analytics {

	use SingletonTrait;

	/**
	 * Option key for storing analytics data.
	 */
	const OPTION_KEY = 'gcow_analytics';

	/**
	 * Option key for daily analytics data.
	 */
	const DAILY_STATS_KEY = 'gcow_analytics_daily';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_analytics_script' ] );
		add_action( 'wp_footer', [ $this, 'maybe_enqueue_analytics_tracker' ], 5 );
		add_action( 'woocommerce_thankyou', [ $this, 'track_order_conversions' ], 10, 1 );
	}

	/**
	 * Register the analytics JS file (not yet enqueued).
	 *
	 * @return void
	 */
	public function register_analytics_script() {
		if ( ! is_checkout() && ! is_cart() ) {
			return;
		}
		wp_register_script(
			'gcow-analytics',
			GCOW_URL . '/assets/js/gcow-analytics.js',
			[ 'jquery' ],
			GCOW_VERSION,
			true
		);
	}

	/**
	 * Enqueue analytics tracker only when bumps are visible on the page.
	 *
	 * @return void
	 */
	public function maybe_enqueue_analytics_tracker() {
		if ( ! is_checkout() && ! is_cart() ) {
			return;
		}
		if ( empty( $this->get_displayed_bumps() ) ) {
			return;
		}
		wp_enqueue_script( 'gcow-analytics' );
		wp_localize_script(
			'gcow-analytics',
			'gcowAnalyticsData',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gcow_analytics_nonce' ),
			]
		);
	}

	/**
	 * Get bumps that are displayed on current page.
	 *
	 * @return array
	 */
	private function get_displayed_bumps() {
		// This will be set by Frontend class when rendering bumps
		global $gcow_displayed_bumps;
		return isset( $gcow_displayed_bumps ) ? $gcow_displayed_bumps : [];
	}

	/**
	 * Track bump impression (AJAX handler).
	 *
	 * @return void
	 */
	public static function ajax_track_impression() {
		check_ajax_referer( 'gcow_analytics_nonce', 'nonce' );

		$bump_id = isset( $_POST['bump_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bump_id'] ) ) : '';

		if ( empty( $bump_id ) ) {
			wp_send_json_error();
		}

		self::record_impression( $bump_id );

		wp_send_json_success();
	}

	/**
	 * Record a bump impression.
	 *
	 * @param string $bump_id Bump ID.
	 * @return void
	 */
	public static function record_impression( $bump_id ) {
		$analytics = get_option( self::OPTION_KEY, [] );

		if ( ! isset( $analytics[ $bump_id ] ) ) {
			$analytics[ $bump_id ] = [
				'impressions' => 0,
				'clicks'      => 0,
				'conversions' => 0,
				'revenue'     => 0,
			];
		}

		$analytics[ $bump_id ]['impressions']++;
		update_option( self::OPTION_KEY, $analytics );

		self::update_daily_stats( 'impressions' );
	}

	/**
	 * Record a bump click (when added to cart).
	 *
	 * @param string $bump_id Bump ID.
	 * @return void
	 */
	public static function record_click( $bump_id ) {
		$analytics = get_option( self::OPTION_KEY, [] );

		if ( ! isset( $analytics[ $bump_id ] ) ) {
			$analytics[ $bump_id ] = [
				'impressions' => 0,
				'clicks'      => 0,
				'conversions' => 0,
				'revenue'     => 0,
			];
		}

		$analytics[ $bump_id ]['clicks']++;

		update_option( self::OPTION_KEY, $analytics );
	}

	/**
	 * Track conversions and revenue when order is completed.
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function track_order_conversions( $order_id ) {
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Check if this order has already been tracked
		$tracked = get_post_meta( $order_id, '_gcow_analytics_tracked', true );
		if ( $tracked ) {
			return;
		}

		// Track each bump product in the order
		foreach ( $order->get_items() as $item ) {
			$bump_data = $item->get_meta( '_gcow_bump' );

			if ( ! empty( $bump_data ) && isset( $bump_data['bump_id'] ) ) {
				$bump_id = $bump_data['bump_id'];
				$revenue = floatval( $item->get_total() );

				self::record_conversion( $bump_id, $revenue );
			}
		}

		// Mark order as tracked
		update_post_meta( $order_id, '_gcow_analytics_tracked', true );
	}

	/**
	 * Record a bump conversion and revenue.
	 *
	 * @param string $bump_id Bump ID.
	 * @param float  $revenue Revenue generated.
	 * @return void
	 */
	public static function record_conversion( $bump_id, $revenue = 0 ) {
		$analytics = get_option( self::OPTION_KEY, [] );

		if ( ! isset( $analytics[ $bump_id ] ) ) {
			$analytics[ $bump_id ] = [
				'impressions' => 0,
				'clicks'      => 0,
				'conversions' => 0,
				'revenue'     => 0,
			];
		}

		$analytics[ $bump_id ]['conversions']++;
		$analytics[ $bump_id ]['revenue'] += floatval( $revenue );
		update_option( self::OPTION_KEY, $analytics );

		self::update_daily_stats( 'conversions', floatval( $revenue ) );
	}

	/**
	 * Update the daily stats option for chart data.
	 *
	 * @param string $field  'impressions' or 'conversions'
	 * @param float  $revenue Revenue to add (for conversions).
	 * @return void
	 */
	private static function update_daily_stats( $field, $revenue = 0.0 ) {
		$today       = current_time( 'Y-m-d' );
		$daily_stats = get_option( self::DAILY_STATS_KEY, [] );

		if ( ! is_array( $daily_stats ) ) {
			$daily_stats = [];
		}

		if ( ! isset( $daily_stats[ $today ] ) ) {
			$daily_stats[ $today ] = [
				'impressions' => 0,
				'conversions' => 0,
				'revenue'     => 0,
			];
		}

		$daily_stats[ $today ][ $field ]++;
		if ( $revenue > 0 ) {
			$daily_stats[ $today ]['revenue'] += $revenue;
		}

		// Keep only last 90 days
		$cutoff = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
		foreach ( array_keys( $daily_stats ) as $date ) {
			if ( $date < $cutoff ) {
				unset( $daily_stats[ $date ] );
			}
		}

		update_option( self::DAILY_STATS_KEY, $daily_stats );
	}

	/**
	 * Get analytics data for a specific bump.
	 *
	 * @param string $bump_id Bump ID.
	 * @return array
	 */
	public static function get_bump_analytics( $bump_id ) {
		$analytics = get_option( self::OPTION_KEY, [] );

		if ( ! isset( $analytics[ $bump_id ] ) ) {
			return [
				'impressions'    => 0,
				'clicks'         => 0,
				'conversions'    => 0,
				'revenue'        => 0,
				'click_rate'     => 0,
				'conversion_rate' => 0,
			];
		}

		$data = $analytics[ $bump_id ];

		// Calculate rates
		$data['click_rate']      = $data['impressions'] > 0 ? ( $data['clicks'] / $data['impressions'] ) * 100 : 0;
		$data['conversion_rate'] = $data['clicks'] > 0 ? ( $data['conversions'] / $data['clicks'] ) * 100 : 0;

		return $data;
	}

	/**
	 * Get all analytics data.
	 *
	 * @return array
	 */
	public static function get_all_analytics() {
		$analytics = get_option( self::OPTION_KEY, [] );
		$result    = [];

		foreach ( $analytics as $bump_id => $data ) {
			$data['click_rate']      = $data['impressions'] > 0 ? ( $data['clicks'] / $data['impressions'] ) * 100 : 0;
			$data['conversion_rate'] = $data['clicks'] > 0 ? ( $data['conversions'] / $data['clicks'] ) * 100 : 0;
			$data['bump_id']         = $bump_id;

			$result[] = $data;
		}

		return $result;
	}

	/**
	 * Reset analytics for a specific bump.
	 *
	 * @param string $bump_id Bump ID.
	 * @return void
	 */
	public static function reset_bump_analytics( $bump_id ) {
		$analytics = get_option( self::OPTION_KEY, [] );

		if ( isset( $analytics[ $bump_id ] ) ) {
			unset( $analytics[ $bump_id ] );
			update_option( self::OPTION_KEY, $analytics );
		}
	}

	/**
	 * Reset all analytics data.
	 *
	 * @return void
	 */
	public static function reset_all_analytics() {
		delete_option( self::OPTION_KEY );
	}
}
