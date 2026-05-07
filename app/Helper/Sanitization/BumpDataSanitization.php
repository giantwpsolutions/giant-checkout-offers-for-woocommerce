<?php
/**
 * Bump Data Sanitization Helper.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Helper\Sanitization;

defined( 'ABSPATH' ) || exit;

/**
 * Sanitizes bump form data before saving.
 */
class BumpDataSanitization {

    /**
     * Valid position values.
     */
    private static $valid_positions = [
        'checkout_before_payment',
        'checkout_after_payment',
        'checkout_before_review',
        'checkout_after_review',
        'cart_before_totals',
        'cart_after_totals',
    ];

    /**
     * Valid display condition values.
     */
    private static $valid_conditions = [
        'all',
        'products',
        'categories',
        'cart_total',
    ];

    /**
     * Valid discount type values.
     */
    private static $valid_discount_types = [
        'percentage',
        'fixed',
    ];

    /**
     * Valid template values.
     */
    private static $valid_templates = [
        'standard',
        'compact',
        'hero',
        'ribbon',
        'split',
        'floating',
        'timeline',
        'social',
    ];

    /**
     * Valid status values.
     */
    private static $valid_statuses = [
        'active',
        'inactive',
        'draft',
    ];

    /**
     * Sanitize bump data.
     *
     * @param array $data Raw bump data from request.
     * @return array Sanitized bump data.
     */
    public static function sanitize( $data ) {

        if ( ! is_array( $data ) ) {
            return [];
        }

        $sanitized = [
            'id'                 => sanitize_text_field( $data['id'] ?? uniqid( 'bump_', true ) ),
            'createdAt'          => sanitize_text_field( $data['createdAt'] ?? current_time( 'c' ) ),
            'updatedAt'          => sanitize_text_field( current_time( 'c' ) ),
            'status'             => self::sanitize_enum( $data['status'] ?? 'active', self::$valid_statuses, 'active' ),

            // Product tab
            'name'               => sanitize_text_field( $data['name'] ?? '' ),
            'product'            => isset( $data['product'] ) ? absint( $data['product'] ) : 0,
            'variationId'        => isset( $data['variationId'] ) ? absint( $data['variationId'] ) : 0,
            'variationAttrs'     => self::sanitize_variation_attrs( $data['variationAttrs'] ?? [] ),
            'discountType'       => self::sanitize_enum( $data['discountType'] ?? 'percentage', self::$valid_discount_types, 'percentage' ),
            'discountValue'      => self::sanitize_number( $data['discountValue'] ?? 10, 0, 9999, 10 ),
            'position'           => self::sanitize_enum( $data['position'] ?? 'checkout_before_payment', self::$valid_positions, 'checkout_before_payment' ),

            // Conditions tab
            'displayCondition'   => self::sanitize_enum( $data['displayCondition'] ?? 'all', self::$valid_conditions, 'all' ),
            'selectedProducts'   => self::sanitize_id_array( $data['selectedProducts'] ?? [] ),
            'selectedCategories' => self::sanitize_id_array( $data['selectedCategories'] ?? [] ),
            'minCartTotal'       => self::sanitize_number( $data['minCartTotal'] ?? 50, 0, 999999, 50 ),
            'excludeBumpProduct' => isset( $data['excludeBumpProduct'] ) ? (bool) $data['excludeBumpProduct'] : true,

            // Design tab
            'title'              => sanitize_text_field( $data['title'] ?? '' ),
            'description'        => sanitize_textarea_field( $data['description'] ?? '' ),
            'buttonText'         => sanitize_text_field( $data['buttonText'] ?? '' ),
            'template'           => self::sanitize_enum( $data['template'] ?? 'standard', self::$valid_templates, 'standard' ),
        ];

        return $sanitized;
    }

    /**
     * Sanitize an enum value against allowed values.
     *
     * @param string $value    The value to check.
     * @param array  $allowed  Allowed values.
     * @param string $default  Default value if invalid.
     * @return string
     */
    private static function sanitize_enum( $value, $allowed, $default ) {
        $value = sanitize_text_field( $value );
        return in_array( $value, $allowed, true ) ? $value : $default;
    }

    /**
     * Sanitize a numeric value with bounds.
     *
     * @param mixed $value   The raw value.
     * @param float $min     Minimum value.
     * @param float $max     Maximum value.
     * @param float $default Default value.
     * @return float
     */
    private static function sanitize_number( $value, $min, $max, $default ) {
        if ( ! is_numeric( $value ) ) {
            return $default;
        }
        return max( $min, min( $max, floatval( $value ) ) );
    }

    /**
     * Sanitize an array of integer IDs.
     *
     * @param mixed $value The raw array.
     * @return array Array of positive integers.
     */
    private static function sanitize_id_array( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        return array_values( array_filter( array_map( 'absint', $value ) ) );
    }

    /**
     * Sanitize variation attribute key-value pairs (e.g. attribute_pa_color => black).
     *
     * @param mixed $value
     * @return array
     */
    private static function sanitize_variation_attrs( $value ) {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $clean = [];
        foreach ( $value as $k => $v ) {
            $clean[ sanitize_key( $k ) ] = sanitize_text_field( $v );
        }
        return $clean;
    }
}
