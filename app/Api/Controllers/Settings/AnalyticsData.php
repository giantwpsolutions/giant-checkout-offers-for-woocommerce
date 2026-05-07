<?php
/**
 * Analytics Data REST API Controller.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Api\Controllers\Settings;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use GiantCheckoutOffers\Analytics\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Handles analytics data retrieval for order bumps.
 */
class AnalyticsData extends WP_REST_Controller {

    /**
     * Option key for storing analytics data.
     */
    const OPTION_KEY = 'gcow_analytics';

    /**
     * Option key for daily stats.
     */
    const DAILY_STATS_KEY = 'gcow_analytics_daily';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'gcow/v2';
        $this->rest_base = 'analytics';
    }

    /**
     * Registers REST API routes.
     *
     * @return void
     */
    public function register_routes() {
        // GET analytics overview
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_analytics' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                    'args'                => [
                        'period' => [
                            'default'     => '7',
                            'type'        => 'string',
                            'description' => 'Period in days (7, 30, 90)',
                        ],
                    ],
                ],
            ]
        );

    }

    /**
     * Permission callback for analytics read endpoints.
     *
     * @return bool
     */
    public function permission_callback() {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Get analytics overview.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_analytics( WP_REST_Request $request ) {
        $period        = absint( $request->get_param( 'period' ) ) ?: 7;
        $all_analytics = Analytics::get_all_analytics();
        $daily_stats   = get_option( self::DAILY_STATS_KEY, [] );
        $bumps         = get_option( BumpData::OPTION_KEY, [] );

        if ( ! is_array( $daily_stats ) ) {
            $daily_stats = [];
        }
        if ( ! is_array( $bumps ) ) {
            $bumps = [];
        }

        // Calculate totals
        $total_impressions = 0;
        $total_clicks      = 0;
        $total_conversions = 0;
        $total_revenue     = 0;
        $bump_stats        = [];

        foreach ( $all_analytics as $stats ) {
            $bump_id   = $stats['bump_id'];
            $bump_name = isset( $bumps[ $bump_id ] ) ? $bumps[ $bump_id ]['name'] : __( 'Deleted Bump', 'giant-checkout-offers-for-woocommerce' );

            $impressions = isset( $stats['impressions'] ) ? (int) $stats['impressions'] : 0;
            $clicks      = isset( $stats['clicks'] ) ? (int) $stats['clicks'] : 0;
            $conversions = isset( $stats['conversions'] ) ? (int) $stats['conversions'] : 0;
            $revenue     = isset( $stats['revenue'] ) ? (float) $stats['revenue'] : 0;

            $total_impressions += $impressions;
            $total_clicks      += $clicks;
            $total_conversions += $conversions;
            $total_revenue     += $revenue;

            $bump_stats[] = [
                'bump_id'         => $bump_id,
                'name'            => $bump_name,
                'impressions'     => $impressions,
                'clicks'          => $clicks,
                'conversions'     => $conversions,
                'click_rate'      => isset( $stats['click_rate'] ) ? round( $stats['click_rate'], 1 ) : 0,
                'conversion_rate' => isset( $stats['conversion_rate'] ) ? round( $stats['conversion_rate'], 1 ) : 0,
                'revenue'         => round( $revenue, 2 ),
            ];
        }

        // Sort bump stats by revenue descending
        usort( $bump_stats, function ( $a, $b ) {
            return $b['revenue'] <=> $a['revenue'];
        } );

        $click_rate = $total_impressions > 0
            ? round( ( $total_clicks / $total_impressions ) * 100, 1 )
            : 0;

        $conversion_rate = $total_clicks > 0
            ? round( ( $total_conversions / $total_clicks ) * 100, 1 )
            : 0;

        // Build daily chart data for the requested period
        $chart_data = $this->build_chart_data( $daily_stats, $period );

        return new WP_REST_Response(
            [
                'success' => true,
                'data'    => [
                    'currency_symbol' => function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '$',
                    'overview'        => [
                        'impressions'     => $total_impressions,
                        'clicks'          => $total_clicks,
                        'conversions'     => $total_conversions,
                        'click_rate'      => $click_rate,
                        'conversion_rate' => $conversion_rate,
                        'revenue'         => round( $total_revenue, 2 ),
                    ],
                    'bump_stats'      => $bump_stats,
                    'chart'           => $chart_data,
                ],
            ],
            200
        );
    }

    /**
     * Build chart data for a given period.
     *
     * @param array $daily_stats Daily stats from DB.
     * @param int   $period      Number of days.
     * @return array
     */
    private function build_chart_data( $daily_stats, $period ) {
        $labels      = [];
        $revenue     = [];
        $conversions = [];

        for ( $i = $period - 1; $i >= 0; $i-- ) {
            $date    = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $label   = gmdate( 'M j', strtotime( "-{$i} days" ) );
            $labels[]      = $label;
            $revenue[]     = isset( $daily_stats[ $date ] ) ? round( (float) $daily_stats[ $date ]['revenue'], 2 ) : 0;
            $conversions[] = isset( $daily_stats[ $date ] ) ? (int) $daily_stats[ $date ]['conversions'] : 0;
        }

        return [
            'labels'      => $labels,
            'revenue'     => $revenue,
            'conversions' => $conversions,
        ];
    }
}
