<?php
/**
 * Post-Purchase Upsell — Thank You Page Handler.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Frontend;

use GiantCheckoutOffers\Traits\SingletonTrait;
use GiantCheckoutOffers\Analytics\Analytics;
use GiantCheckoutOffers\Frontend\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Renders a one-click upsell offer on the WooCommerce thank-you page
 * and handles creating a new order when the customer accepts.
 */
class PostPurchase {

	use SingletonTrait;

	public function __construct() {
		// Bumps explicitly set to "Thank You Page" position.
		add_action( 'woocommerce_thankyou', [ $this, 'display_upsell' ], 10, 1 );

		// Fallback: if no thankyou_page bump is configured, show any active bump
		// inside the order summary (after the order items table).
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'display_order_summary_upsell' ], 10, 1 );

		add_action( 'wp_enqueue_scripts',                       [ $this, 'enqueue_assets' ] );
		add_action( 'wp_ajax_gcow_post_purchase_accept',        [ $this, 'ajax_accept' ] );
		add_action( 'wp_ajax_nopriv_gcow_post_purchase_accept', [ $this, 'ajax_accept' ] );
	}

	// ── Assets ──────────────────────────────────────────────────────────────────

	public function enqueue_assets() {
		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		wp_enqueue_style(
			'gcow-post-purchase',
			GCOW_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			GCOW_VERSION
		);

		wp_enqueue_script(
			'gcow-post-purchase',
			GCOW_PLUGIN_URL . 'assets/js/post-purchase.js',
			[ 'jquery' ],
			GCOW_VERSION,
			true
		);

		wp_localize_script(
			'gcow-post-purchase',
			'gcowPostPurchase',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gcow_post_purchase_nonce' ),
				'i18n'    => [
					'accept'     => __( 'Yes! Add This to My Order', 'giant-checkout-offers-for-woocommerce' ),
					'decline'    => __( "No thanks, I don't want this offer.", 'giant-checkout-offers-for-woocommerce' ),
					'processing' => __( 'Processing…', 'giant-checkout-offers-for-woocommerce' ),
					'error'      => __( 'Something went wrong. Please try again.', 'giant-checkout-offers-for-woocommerce' ),
				],
			]
		);
	}

	// ── Display ─────────────────────────────────────────────────────────────────

	/**
	 * Show bumps explicitly configured for the thank-you page position.
	 * Fires via woocommerce_thankyou (after order details section).
	 */
	public function display_upsell( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$bump = $this->get_thankyou_bump( $order );
		if ( ! $bump ) {
			return;
		}

		$this->render( $bump, $order );
	}

	/**
	 * Fallback: if no bump is configured for the thank-you page, show any
	 * active bump right after the order summary table.
	 * Fires via woocommerce_order_details_after_order_table.
	 *
	 * @param \WC_Order $order
	 */
	public function display_order_summary_upsell( $order ) {
		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		// Active thankyou_page bumps take priority — they render via display_upsell().
		if ( $this->has_thankyou_page_bumps() ) {
			return;
		}

		$bump = $this->get_fallback_bump( $order );
		if ( ! $bump ) {
			return;
		}

		$this->render( $bump, $order );
	}

	/**
	 * Find the first active bump with position = thankyou_page that meets conditions.
	 */
	private function get_thankyou_bump( $order ) {
		foreach ( get_option( 'gcow_bumps', [] ) as $bump ) {
			if ( ( $bump['status'] ?? '' ) !== 'active' ) {
				continue;
			}
			if ( ( $bump['position'] ?? '' ) !== 'thankyou_page' ) {
				continue;
			}
			if ( $this->conditions_met( $bump, $order ) ) {
				return $bump;
			}
		}

		return null;
	}

	/**
	 * Find any active bump (any position) that meets conditions — used as fallback.
	 */
	private function get_fallback_bump( $order ) {
		foreach ( get_option( 'gcow_bumps', [] ) as $bump ) {
			if ( ( $bump['status'] ?? '' ) !== 'active' ) {
				continue;
			}
			if ( $this->conditions_met( $bump, $order ) ) {
				return $bump;
			}
		}

		return null;
	}

	/**
	 * Returns true if at least one ACTIVE bump is set to thankyou_page position.
	 * Inactive/draft thankyou_page bumps do not block the fallback display.
	 */
	private function has_thankyou_page_bumps() {
		foreach ( get_option( 'gcow_bumps', [] ) as $bump ) {
			if ( ( $bump['position'] ?? '' ) === 'thankyou_page' && ( $bump['status'] ?? '' ) === 'active' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Evaluate display conditions against the completed order (not the cart).
	 */
	private function conditions_met( $bump, $order ) {
		$condition          = $bump['displayCondition'] ?? 'all';
		$order_product_ids  = [];
		$order_category_ids = [];

		foreach ( $order->get_items() as $item ) {
			$pid                 = (int) $item->get_product_id();
			$order_product_ids[] = $pid;

			if ( $condition === 'categories' && ! empty( $bump['selectedCategories'] ) ) {
				$product = wc_get_product( $pid );
				if ( $product ) {
					$order_category_ids = array_merge( $order_category_ids, $product->get_category_ids() );
				}
			}
		}

		// Exclude if the bump product is already in the completed order.
		if ( ! empty( $bump['excludeBumpProduct'] ) && ! empty( $bump['product'] ) ) {
			if ( in_array( (int) $bump['product'], $order_product_ids, true ) ) {
				return false;
			}
		}

		if ( $condition === 'all' ) {
			return true;
		}

		if ( $condition === 'products' && ! empty( $bump['selectedProducts'] ) ) {
			return ! empty( array_intersect( $bump['selectedProducts'], $order_product_ids ) );
		}

		if ( $condition === 'categories' && ! empty( $bump['selectedCategories'] ) ) {
			return ! empty( array_intersect( $bump['selectedCategories'], array_unique( $order_category_ids ) ) );
		}

		if ( $condition === 'cart_total' && isset( $bump['minCartTotal'] ) ) {
			return (float) $order->get_subtotal() >= (float) $bump['minCartTotal'];
		}

		return true;
	}

	/**
	 * Build display data and load the shared bump template.
	 * Uses the same template + style system as checkout bumps.
	 */
	private function render( $bump, $order ) {
		$product_id = (int) ( $bump['product'] ?? 0 );
		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return;
		}

		// Use regular price; fall back to current price for products without a separate regular price.
		$regular_price = (float) $product->get_regular_price();
		if ( $regular_price <= 0 ) {
			$regular_price = (float) $product->get_price();
		}
		if ( $regular_price <= 0 ) {
			return;
		}

		$discount_type  = $bump['discountType']  ?? 'percentage';
		$discount_value = (float) ( $bump['discountValue'] ?? 0 );

		$discounted_price = $discount_type === 'percentage'
			? $regular_price - ( $regular_price * $discount_value / 100 )
			: $regular_price - $discount_value;

		$discounted_price = max( 0, $discounted_price );

		// Resolve template + styles — same logic as Frontend::render_bump().
		$settings = get_option( 'gcow_settings', [] );
		$template  = $bump['template'] ?? $settings['selectedTemplate'] ?? 'standard';

		// Timeline and Hero are designed for the cart page only — skip on thank-you page.
		if ( in_array( $template, [ 'timeline', 'hero' ], true ) ) {
			return;
		}

		$styles = Frontend::instance()->get_template_styles( $template );

		$bump_data = [
			'id'                   => $bump['id'] ?? uniqid( 'gcow_pp_' ),
			'product_id'           => $product_id,
			'variation_id'         => 0,
			'variation_attributes' => [],
			'product'              => $product,
			'title'                => ! empty( $bump['title'] ) ? $bump['title'] : $product->get_name(),
			'description'          => $bump['description'] ?? '',
			'button_text'          => ! empty( $bump['buttonText'] ) ? $bump['buttonText'] : __( 'Yes! Add This to My Order', 'giant-checkout-offers-for-woocommerce' ),
			'discount_type'        => $discount_type,
			'discount_value'       => $discount_value,
			'regular_price'        => wc_price( $regular_price ),
			'discounted_price'     => wc_price( $discounted_price ),
			'saved_price'          => wc_price( $regular_price - $discounted_price ),
			'image_url'            => wp_get_attachment_url( $product->get_image_id() ) ?: wc_placeholder_img_src(),
			'variations'           => [],
			'attribute_selectors'  => [],
			'variations_json'      => '[]',
		];

		$template_file = GCOW_PLUGIN_PATH . "app/Frontend/templates/{$template}.php";
		if ( ! file_exists( $template_file ) ) {
			$template_file = GCOW_PLUGIN_PATH . 'app/Frontend/templates/standard.php';
		}

		// Wrap in a context div so JS knows this is a post-purchase bump and can
		// swap the checkout checkbox CTA for Accept / Decline buttons.
		?>
		<div class="gcow-pp-wrap"
			data-order-id="<?php echo esc_attr( $order->get_id() ); ?>"
			data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
			style="margin: 32px 0;">
			<?php include $template_file; ?>
		</div>
		<?php
	}

	// ── AJAX ────────────────────────────────────────────────────────────────────

	/**
	 * Create a new pending order for the accepted upsell and return its pay URL.
	 */
	public function ajax_accept() {
		check_ajax_referer( 'gcow_post_purchase_nonce', 'nonce' );

		$order_id   = absint( $_POST['order_id']   ?? 0 );
		$product_id = absint( $_POST['product_id'] ?? 0 );
		$bump_id    = sanitize_text_field( $_POST['bump_id'] ?? '' );

		if ( ! $order_id || ! $product_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid request.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		$original = wc_get_order( $order_id );
		if ( ! $original ) {
			wp_send_json_error( [ 'message' => __( 'Order not found.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			wp_send_json_error( [ 'message' => __( 'Product is not available.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		$price = $this->calculate_discounted_price( $product, $bump_id );

		$new_order = wc_create_order( [
			'customer_id' => $original->get_customer_id(),
			'status'      => 'pending',
		] );

		if ( is_wp_error( $new_order ) ) {
			wp_send_json_error( [ 'message' => __( 'Failed to create order. Please try again.', 'giant-checkout-offers-for-woocommerce' ) ] );
		}

		$new_order->set_address( $original->get_address( 'billing' ),  'billing' );
		$new_order->set_address( $original->get_address( 'shipping' ), 'shipping' );
		$new_order->set_payment_method( $original->get_payment_method() );
		$new_order->set_payment_method_title( $original->get_payment_method_title() );
		$new_order->set_currency( $original->get_currency() );

		$item = new \WC_Order_Item_Product();
		$item->set_product( $product );
		$item->set_quantity( 1 );
		$item->set_subtotal( $price );
		$item->set_total( $price );
		$item->add_meta_data( '_gcow_post_purchase', [
			'bump_id'        => $bump_id,
			'original_order' => $order_id,
		], true );

		$new_order->add_item( $item );
		$new_order->add_meta_data( '_gcow_post_purchase_bump', $bump_id, true );
		$new_order->add_meta_data( '_gcow_original_order_id',  $order_id,  true );
		$new_order->calculate_totals();
		$new_order->save();

		Analytics::record_click( $bump_id );

		wp_send_json_success( [ 'pay_url' => $new_order->get_checkout_payment_url() ] );
	}

	private function calculate_discounted_price( $product, $bump_id ) {
		$regular_price = (float) $product->get_regular_price();
		if ( $regular_price <= 0 ) {
			$regular_price = (float) $product->get_price();
		}

		foreach ( get_option( 'gcow_bumps', [] ) as $bump ) {
			if ( ( $bump['id'] ?? '' ) !== $bump_id ) {
				continue;
			}

			$type  = $bump['discountType']  ?? 'percentage';
			$value = (float) ( $bump['discountValue'] ?? 0 );

			$price = $type === 'percentage'
				? $regular_price - ( $regular_price * $value / 100 )
				: $regular_price - $value;

			return max( 0, $price );
		}

		return $regular_price;
	}
}
