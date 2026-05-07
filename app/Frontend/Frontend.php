<?php
/**
 * Frontend Handler - Displays order bumps to customers.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Frontend;

use GiantCheckoutOffers\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Handles frontend display of order bumps on checkout/cart.
 */
class Frontend {

	use SingletonTrait;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Checkout positions
		add_action( 'woocommerce_review_order_before_payment', [ $this, 'display_order_bump' ], 10 );
		add_action( 'woocommerce_review_order_after_payment',  [ $this, 'display_order_bump' ], 10 );
		add_action( 'woocommerce_checkout_before_order_review', [ $this, 'display_order_bump' ], 10 );
		add_action( 'woocommerce_checkout_after_order_review',  [ $this, 'display_order_bump' ], 10 );

		// Cart positions
		add_action( 'woocommerce_before_cart_totals', [ $this, 'display_order_bump' ], 10 );
		add_action( 'woocommerce_after_cart_totals',  [ $this, 'display_order_bump' ], 10 );

		// Enqueue frontend assets
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// AJAX handler for adding bump to cart
		add_action( 'wp_ajax_gcow_add_bump_to_cart', [ $this, 'ajax_add_bump_to_cart' ] );
		add_action( 'wp_ajax_nopriv_gcow_add_bump_to_cart', [ $this, 'ajax_add_bump_to_cart' ] );

		// Apply bump discount to cart items
		add_filter( 'woocommerce_cart_item_price', [ $this, 'modify_cart_item_price' ], 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'apply_bump_discount' ], 10, 1 );

		// Show bump discount in cart item name
		// Badge intentionally not shown to customers on cart/checkout.

		// Save bump meta to order items
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_bump_meta_to_order_item' ], 10, 4 );
	}

	/**
	 * Enqueue frontend CSS and JS.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_checkout() && ! is_cart() ) {
			return;
		}

		// Enqueue main plugin styles (Tailwind included)
		wp_enqueue_style(
			'gcow-frontend',
			GCOW_PLUGIN_URL . 'dist/assets/main.css',
			[],
			time()
		);

		// Enqueue frontend JS
		wp_enqueue_script(
			'gcow-frontend',
			GCOW_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'jquery' ],
			time(),
			true
		);

		wp_localize_script(
			'gcow-frontend',
			'gcowFrontend',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gcow_frontend_nonce' ),
			]
		);
	}

	/**
	 * Display order bump on checkout page.
	 *
	 * @return void
	 */
	/**
	 * Map WooCommerce action hooks to bump position slugs.
	 */
	private static $hook_to_position = [
		'woocommerce_review_order_before_payment'  => 'checkout_before_payment',
		'woocommerce_review_order_after_payment'   => 'checkout_after_payment',
		'woocommerce_checkout_before_order_review' => 'checkout_before_review',
		'woocommerce_checkout_after_order_review'  => 'checkout_after_review',
		'woocommerce_before_cart_totals'           => 'cart_before_totals',
		'woocommerce_after_cart_totals'            => 'cart_after_totals',
	];

	public function display_order_bump() {
		// Determine which position triggered this call
		$current_hook    = current_filter();
		$current_position = self::$hook_to_position[ $current_hook ] ?? null;

		// Get active bumps
		$bumps = $this->get_active_bumps();

		if ( empty( $bumps ) ) {
			return;
		}

		global $gcow_displayed_bumps;
		if ( ! isset( $gcow_displayed_bumps ) ) {
			$gcow_displayed_bumps = [];
		}

		foreach ( $bumps as $bump ) {
			// Only render if bump position matches current hook
			$bump_position = $bump['position'] ?? 'checkout_before_payment';
			if ( $current_position && $bump_position !== $current_position ) {
				continue;
			}

			if ( isset( $bump['id'] ) ) {
				$gcow_displayed_bumps[] = $bump['id'];
			}
			$this->render_bump( $bump );
		}
	}

	/**
	 * Get all active bumps that should be displayed.
	 *
	 * @return array
	 */
	private function get_active_bumps() {
		$bumps_option = get_option( 'gcow_bumps', [] );

		if ( empty( $bumps_option ) || ! is_array( $bumps_option ) ) {
			return [];
		}

		$active_bumps = [];

		foreach ( $bumps_option as $bump ) {
			if ( ! isset( $bump['status'] ) || $bump['status'] !== 'active' ) {
				continue;
			}
			if ( ! $this->check_bump_conditions( $bump ) ) {
				continue;
			}
			$active_bumps[] = $bump;
		}

		// AI mode (Pro): select the single best bump for the current cart
		$ai_class = class_exists( 'GiantCheckoutOffersPro\Api\Controllers\AiEngine\AiEngineSettings' )
			? 'GiantCheckoutOffersPro\Api\Controllers\AiEngine\AiEngineSettings'
			: null;

		if ( ! empty( $active_bumps ) && $ai_class && get_option( 'gcow_ai_enabled', false ) ) {
			$cart_product_ids = [];
			if ( WC()->cart ) {
				foreach ( WC()->cart->get_cart() as $item ) {
					$cart_product_ids[] = (int) $item['product_id'];
				}
			}
			$ai_bump_id = $ai_class::get_ai_bump_for_cart( $cart_product_ids );
			if ( $ai_bump_id ) {
				foreach ( $active_bumps as $bump ) {
					if ( ( $bump['id'] ?? '' ) === $ai_bump_id ) {
						return [ $bump ]; // show only the AI-selected bump
					}
				}
			}
			// AI enabled but no match — show nothing to avoid irrelevant bumps
			return [];
		}

		return $active_bumps;
	}

	/**
	 * Check if bump display conditions are met.
	 *
	 * @param array $bump Bump data.
	 * @return bool
	 */
	private function check_bump_conditions( $bump ) {
		$condition = isset( $bump['displayCondition'] ) ? $bump['displayCondition'] : 'all';

		// Always show
		if ( $condition === 'all' ) {
			return true;
		}

		$cart = WC()->cart;
		if ( ! $cart ) {
			return false;
		}

		// Check if bump product is already in cart
		if ( isset( $bump['excludeBumpProduct'] ) && $bump['excludeBumpProduct'] && isset( $bump['product'] ) ) {
			foreach ( $cart->get_cart() as $cart_item ) {
				if ( $cart_item['product_id'] == $bump['product'] ) {
					return false; // Bump product already in cart
				}
			}
		}

		// Check specific products in cart
		if ( $condition === 'products' && ! empty( $bump['selectedProducts'] ) ) {
			$cart_product_ids = [];
			foreach ( $cart->get_cart() as $cart_item ) {
				$cart_product_ids[] = $cart_item['product_id'];
			}
			$has_match = array_intersect( $bump['selectedProducts'], $cart_product_ids );
			return ! empty( $has_match );
		}

		// Check specific categories in cart
		if ( $condition === 'categories' && ! empty( $bump['selectedCategories'] ) ) {
			$cart_category_ids = [];
			foreach ( $cart->get_cart() as $cart_item ) {
				$product = wc_get_product( $cart_item['product_id'] );
				if ( $product ) {
					$cart_category_ids = array_merge( $cart_category_ids, $product->get_category_ids() );
				}
			}
			$cart_category_ids = array_unique( $cart_category_ids );
			$has_match         = array_intersect( $bump['selectedCategories'], $cart_category_ids );
			return ! empty( $has_match );
		}

		// Check cart total
		if ( $condition === 'cart_total' && isset( $bump['minCartTotal'] ) ) {
			return $cart->get_subtotal() >= floatval( $bump['minCartTotal'] );
		}

		return true;
	}

	/**
	 * Render a single bump.
	 *
	 * @param array $bump Bump data.
	 * @return void
	 */
	private function render_bump( $bump ) {
		// Get product
		$product_id = isset( $bump['product'] ) ? intval( $bump['product'] ) : 0;
		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		// Handle variable products
		$variation_id         = 0;
		$variation_attributes = [];
		$all_variations       = []; // For "All variations" mode — customer selects
		$attribute_selectors  = []; // For per-attribute dropdowns in 'all variations' mode
		$specified_variation  = intval( $bump['variationId'] ?? 0 );

		if ( $product->is_type( 'variable' ) ) {
			$variable_product   = new \WC_Product_Variable( $product_id );
			$available_variants = $variable_product->get_available_variations();

			if ( empty( $available_variants ) ) {
				return;
			}

			if ( $specified_variation > 0 ) {
				// Admin specified a fixed variation — use it directly
				$variation_product = new \WC_Product_Variation( $specified_variation );
				if ( ! $variation_product->exists() || ! $variation_product->is_purchasable() || ! $variation_product->is_in_stock() ) {
					return;
				}
				$variation_id = $specified_variation;
				// Use admin-stored specific attrs (covers "Any" attribute values chosen by admin)
				// Fall back to what the variation product itself defines
				$stored_attrs = isset( $bump['variationAttrs'] ) && is_array( $bump['variationAttrs'] ) ? $bump['variationAttrs'] : [];
				if ( ! empty( $stored_attrs ) ) {
					$variation_attributes = $stored_attrs;
				} else {
					$raw = $variation_product->get_variation_attributes(); // has attribute_ prefix
					$variation_attributes = array_filter( $raw, fn( $v ) => $v !== '' );
				}
				$product = $variation_product;
			} else {
				// "All variations" — customer selects on checkout page
				// Build a clean variations list for the template
				foreach ( $available_variants as $var ) {
					if ( ! $var['is_in_stock'] || ! $var['is_purchasable'] ) {
						continue;
					}
					$var_product = wc_get_product( $var['variation_id'] );
					if ( ! $var_product ) {
						continue;
					}
					// Build human-readable label from attributes (skip "Any"/empty attributes)
					$attr_labels = [];
					foreach ( $var['attributes'] as $attr_key => $attr_value ) {
						if ( ! $attr_value ) {
							continue;
						}
						$taxonomy      = str_replace( 'attribute_', '', $attr_key );
						$attr_label    = wc_attribute_label( $taxonomy );
						$attr_labels[] = $attr_label . ': ' . ucfirst( $attr_value );
					}
					$all_variations[] = [
						'id'         => $var['variation_id'],
						'label'      => implode( ' / ', $attr_labels ) ?: ( 'Variation #' . $var['variation_id'] ),
						'price_html' => $var_product->get_price_html(),
						'image_url'  => wp_get_attachment_url( $var_product->get_image_id() ) ?: '',
						'attributes' => $var['attributes'],
					];
				}
				if ( empty( $all_variations ) ) {
					return;
				}
				// Use first variation for initial pricing display
				$first_var    = wc_get_product( $all_variations[0]['id'] );
				$variation_id = $all_variations[0]['id'];
				$product      = $first_var ?: $product;
				// Build per-attribute dropdowns for the template
				$product_variation_attrs = $variable_product->get_variation_attributes();
				foreach ( $product_variation_attrs as $taxonomy => $terms ) {
					$attr_key = 'attribute_' . $taxonomy;
					$attr_label = wc_attribute_label( $taxonomy );
					$options  = [];
					foreach ( $terms as $slug ) {
						if ( taxonomy_exists( $taxonomy ) ) {
							$term = get_term_by( 'slug', $slug, $taxonomy );
							$name = $term ? $term->name : ucfirst( $slug );
						} else {
							$name = ucfirst( $slug );
						}
						$options[] = [ 'value' => $slug, 'label' => $name ];
					}
					if ( ! empty( $options ) ) {
						$attribute_selectors[ $attr_key ] = [ 'label' => $attr_label, 'options' => $options ];
					}
				}
			}
		}

		// Get template — prefer bump-level, then global selection, then default
		$settings = get_option( 'gcow_settings', [] );
		$template = $bump['template']
			?? $settings['selectedTemplate']
			?? 'standard';

		// Get template styles
		$styles = $this->get_template_styles( $template );

		// Prepare bump data
		$bump_data = [
			'id'                   => isset( $bump['id'] ) ? $bump['id'] : uniqid( 'bump_' ),
			'product_id'           => $product_id,
			'variation_id'         => $variation_id,
			'variation_attributes' => $variation_attributes,
			'product'              => $product,
			'title'                => isset( $bump['title'] ) && ! empty( $bump['title'] ) ? $bump['title'] : $product->get_name(),
			'description'          => isset( $bump['description'] ) ? $bump['description'] : '',
			'button_text'          => isset( $bump['buttonText'] ) ? $bump['buttonText'] : __( 'Yes, Add to Order!', 'giant-checkout-offers-for-woocommerce' ),
			'discount_type'        => isset( $bump['discountType'] ) ? $bump['discountType'] : 'percentage',
			'discount_value'       => isset( $bump['discountValue'] ) ? floatval( $bump['discountValue'] ) : 0,
			'variations'           => $all_variations,
			'attribute_selectors'  => $attribute_selectors,
			'variations_json'      => wp_json_encode( $all_variations ),
			'styles'               => $styles,
		];

		// Calculate prices
		$regular_price = floatval( $product->get_regular_price() );

		// Skip if product has no price
		if ( $regular_price <= 0 ) {
			return;
		}

		$discount = $bump_data['discount_value'];

		if ( $bump_data['discount_type'] === 'percentage' ) {
			$discounted_price = $regular_price - ( $regular_price * $discount / 100 );
		} else {
			$discounted_price = $regular_price - $discount;
		}

		$saved_amount                  = $regular_price - max( 0, $discounted_price );
		$bump_data['regular_price']    = wc_price( $regular_price ) ?: '';
		$bump_data['discounted_price'] = wc_price( max( 0, $discounted_price ) ) ?: '';
		$bump_data['saved_price']      = wc_price( $saved_amount ) ?: '';
		$bump_data['image_url']        = wp_get_attachment_url( $product->get_image_id() ) ?: '';

		// Load template file
		$template_file = GCOW_PLUGIN_PATH . "app/Frontend/templates/{$template}.php";

		if ( ! file_exists( $template_file ) ) {
			$template_file = GCOW_PLUGIN_PATH . 'app/Frontend/templates/standard.php';
		}

		include_once $template_file;
	}

	/**
	 * Get template styles.
	 *
	 * @param string $template_id Template ID.
	 * @return array
	 */
	private function get_template_styles( $template_id ) {
		$settings        = get_option( 'gcow_settings', [] );
		$template_styles = isset( $settings['templateStyles'] ) ? $settings['templateStyles'] : [];

		// Per-template defaults — must match src/config/templates.js defaultStyles exactly.
		$template_defaults = [
			'standard' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#16a34a',
				'buttonColor'      => '#3b82f6',
				'buttonTextColor'  => '#374151',
				'borderColor'      => '#93c5fd',
				'borderStyle'      => 'dashed',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 12,
				'headerBgColor'    => '#3b82f6',
				'headerTextColor'  => '#ffffff',
				'ctaBgColor'       => '#eff6ff',
				'ctaBorderColor'   => '#bfdbfe',
			],
			'compact' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#9ca3af',
				'priceColor'       => '#111827',
				'buttonColor'      => '#3b82f6',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 12,
				'saveBadgeColor'   => '#059669',
			],
			'hero' => [
				'backgroundColor'  => '#4f46e5',
				'textColor'        => '#ffffff',
				'descriptionColor' => '#c7d2fe',
				'priceColor'       => '#ffffff',
				'buttonColor'      => '#ffffff',
				'buttonTextColor'  => '#4f46e5',
				'borderColor'      => '#4f46e5',
				'borderStyle'      => 'none',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 16,
			],
			'ribbon' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#111827',
				'buttonColor'      => '#f59e0b',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 12,
				'ribbonColor'      => '#ef4444',
				'accentColor'      => '#d97706',
			],
			'split' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#0d9488',
				'buttonColor'      => '#14b8a6',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 0,
				'toggleColor'      => '#14b8a6',
				'starColor'        => '#facc15',
			],
			'floating' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#f43f5e',
				'buttonColor'      => '#f43f5e',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#f3f4f6',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 12,
				'padding'          => 12,
				'badgeColor'       => '#f43f5e',
				'badgeTextColor'   => '#ffffff',
			],
			'timeline' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#7c3aed',
				'buttonColor'      => '#8b5cf6',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 12,
				'accentColor'      => '#8b5cf6',
			],
			'social' => [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#059669',
				'buttonColor'      => '#10b981',
				'buttonTextColor'  => '#1f2937',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 10,
				'ctaBgColor'       => '#ecfdf5',
				'ctaBorderColor'   => '#a7f3d0',
			],
		];

		$defaults = $template_defaults[ $template_id ] ?? [
				'backgroundColor'  => '#ffffff',
				'textColor'        => '#1f2937',
				'descriptionColor' => '#6b7280',
				'priceColor'       => '#16a34a',
				'buttonColor'      => '#3b82f6',
				'buttonTextColor'  => '#ffffff',
				'borderColor'      => '#e5e7eb',
				'borderStyle'      => 'solid',
				'borderWidth'      => 1,
				'borderRadius'     => 8,
				'padding'          => 12,
			];

		// Merge saved (admin-customised) styles on top of per-template defaults
		$saved = isset( $template_styles[ $template_id ] ) ? $template_styles[ $template_id ] : [];

		return array_merge( $defaults, $saved );
	}

	/**
	 * AJAX handler - Add bump product to cart.
	 *
	 * @return void
	 */
	public function ajax_add_bump_to_cart() {
		check_ajax_referer( 'gcow_frontend_nonce', 'nonce' );

		// Ensure WooCommerce is loaded
		if ( ! function_exists( 'WC' ) ) {
			wp_send_json_error( [ 'message' => __( 'WooCommerce is not available.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		// Initialize WooCommerce cart if needed
		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}

		$product_id   = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
		$bump_id      = isset( $_POST['bump_id'] ) ? sanitize_text_field( wp_unslash( $_POST['bump_id'] ) ) : '';

		if ( ! $product_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid product ID.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		// Verify product exists — use variation if provided
		$product_to_check = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );
		if ( ! $product_to_check ) {
			wp_send_json_error( [ 'message' => __( 'Product not found.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		// Check if product is purchasable
		if ( ! $product_to_check->is_purchasable() ) {
			wp_send_json_error( [ 'message' => __( 'This product cannot be purchased.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		// Check stock
		if ( ! $product_to_check->is_in_stock() ) {
			wp_send_json_error( [ 'message' => __( 'Product is out of stock.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		// Get bump data to apply discount
		$bump = $this->get_bump_by_id( $bump_id );

		// Add to cart with bump meta data
		$cart_item_data = [];
		if ( $bump ) {
			$cart_item_data['gcow_bump'] = [
				'bump_id'        => $bump_id,
				'discount_type'  => $bump['discountType'] ?? 'percentage',
				'discount_value' => isset( $bump['discountValue'] ) ? floatval( $bump['discountValue'] ) : 0,
			];
		}

		// Resolve variation attributes for cart.
		// Prefer customer-posted attributes (includes "Any" values they selected).
		// Fallback: read fixed attributes from the variation product itself.
		$variation_attributes = [];
		$posted_attrs_raw     = isset( $_POST['variation_attrs'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_attrs'] ) ) : '';
		if ( $posted_attrs_raw ) {
			$decoded = json_decode( $posted_attrs_raw, true );
			if ( is_array( $decoded ) ) {
				foreach ( $decoded as $k => $v ) {
					$variation_attributes[ sanitize_key( $k ) ] = sanitize_text_field( $v );
				}
			}
		}
		if ( empty( $variation_attributes ) && $variation_id ) {
			// Fallback for specified-variation bumps (no customer-selected attrs)
			$variation_product = new \WC_Product_Variation( $variation_id );
			if ( $variation_product->exists() ) {
				$raw_attrs = $variation_product->get_variation_attributes();
				// Keep only attributes that have a specific value (skip "Any"/empty)
				$variation_attributes = array_filter( $raw_attrs, fn( $v ) => $v !== '' );
			}
		}

		// Add to cart
		try {
			$added = WC()->cart->add_to_cart( $product_id, 1, $variation_id, $variation_attributes, $cart_item_data );

			if ( $added ) {
				// Track click in analytics
				if ( ! empty( $bump_id ) ) {
					\GiantCheckoutOffers\Analytics\Analytics::record_click( $bump_id );
				}

				wp_send_json_success( [
					'message'  => __( 'Product added to cart!', 'giant-checkout-offers-for-woocommerce' ),
					'cart_url' => wc_get_cart_url(),
				] );
			} else {
				$wc_notices = wc_get_notices( 'error' );
				$wc_message = '';
				if ( ! empty( $wc_notices ) ) {
					$first      = reset( $wc_notices );
					$wc_message = is_array( $first ) ? ( $first['notice'] ?? '' ) : (string) $first;
					$wc_message = wp_strip_all_tags( $wc_message );
				}
				wp_send_json_error( [
					'message' => $wc_message ?: __( 'Failed to add product to cart. Please try again.', 'giant-checkout-offers-for-woocommerce' ),
				] );
			}
		} catch ( \Exception $e ) {
			wp_send_json_error( [
				/* translators: %s: error message */
				'message' => sprintf( __( 'Error: %s', 'giant-checkout-offers-for-woocommerce' ), $e->getMessage() ),
			] );
		}
	}

	/**
	 * Get bump by ID.
	 *
	 * @param string $bump_id Bump ID.
	 * @return array|null
	 */
	private function get_bump_by_id( $bump_id ) {
		$bumps = get_option( 'gcow_bumps', [] );

		if ( isset( $bumps[ $bump_id ] ) ) {
			return $bumps[ $bump_id ];
		}

		return null;
	}

	/**
	 * Apply bump discount to cart items before total calculation.
	 *
	 * @param WC_Cart $cart Cart object.
	 * @return void
	 */
	public function apply_bump_discount( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['gcow_bump'] ) ) {
				$bump_data = $cart_item['gcow_bump'];
				$product   = $cart_item['data'];

				$regular_price    = floatval( $product->get_regular_price() );
				$discount_type    = $bump_data['discount_type'];
				$discount_value   = floatval( $bump_data['discount_value'] );

				if ( $discount_type === 'percentage' ) {
					$discounted_price = $regular_price - ( $regular_price * $discount_value / 100 );
				} else {
					$discounted_price = $regular_price - $discount_value;
				}

				$product->set_price( max( 0, $discounted_price ) );
			}
		}
	}

	/**
	 * Modify cart item price display to show discount.
	 *
	 * @param string $price_html Price HTML.
	 * @param array  $cart_item  Cart item.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function modify_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
		if ( isset( $cart_item['gcow_bump'] ) ) {
			$bump_data = $cart_item['gcow_bump'];
			$product   = $cart_item['data'];

			$regular_price    = floatval( $product->get_regular_price() );
			$discount_value   = floatval( $bump_data['discount_value'] );
			$discount_type    = $bump_data['discount_type'];

			if ( $discount_type === 'percentage' ) {
				$discounted_price = $regular_price - ( $regular_price * $discount_value / 100 );
			} else {
				$discounted_price = $regular_price - $discount_value;
			}

			$price_html = '<del>' . wc_price( $regular_price ) . '</del> <ins>' . wc_price( max( 0, $discounted_price ) ) . '</ins>';
		}

		return $price_html;
	}

	/**
	 * Add bump badge to cart item name.
	 *
	 * @param string $product_name Product name.
	 * @param array  $cart_item    Cart item.
	 * @param string $cart_item_key Cart item key.
	 * @return string
	 */
	public function add_bump_badge_to_cart_item( $product_name, $cart_item, $cart_item_key ) {
		if ( isset( $cart_item['gcow_bump'] ) ) {
			$bump_data      = $cart_item['gcow_bump'];
			$discount_value = floatval( $bump_data['discount_value'] );
			$discount_type  = $bump_data['discount_type'];

			$badge_text = sprintf(
				/* translators: 1: discount amount, 2: discount symbol (% or decimal separator) */
				__( 'Order Bump: Save %1$s%2$s', 'giant-checkout-offers-for-woocommerce' ),
				$discount_value,
				$discount_type === 'percentage' ? '%' : wc_get_price_decimal_separator()
			);

			$product_name .= sprintf(
				' <span style="display: inline-block; background-color: #10b981; color: #ffffff; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">%s</span>',
				esc_html( $badge_text )
			);
		}

		return $product_name;
	}

	/**
	 * Save bump meta data to order line item.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values Cart item values.
	 * @param WC_Order              $order Order object.
	 * @return void
	 */
	public function save_bump_meta_to_order_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['gcow_bump'] ) ) {
			$item->add_meta_data( '_gcow_bump', $values['gcow_bump'], true );
		}
	}
}
