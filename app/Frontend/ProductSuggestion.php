<?php
/**
 * Auto Product Suggestion — slider after short description on single product pages.
 *
 * @package GiantCheckoutOffers
 */

namespace GiantCheckoutOffers\Frontend;

use GiantCheckoutOffers\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class ProductSuggestion {

	use SingletonTrait;

	public function __construct() {
		add_action( 'wp_enqueue_scripts',                 [ $this, 'enqueue_assets' ] );
		add_action( 'woocommerce_single_product_summary', [ $this, 'render_slider' ], 25 );
	}

	public function enqueue_assets(): void {
		if ( ! is_product() || ! $this->is_enabled() ) {
			return;
		}

		wp_enqueue_style(
			'gcow-product-suggestion',
			GCOW_PLUGIN_URL . 'assets/css/product-suggestion.css',
			[],
			GCOW_VERSION
		);

		wp_enqueue_script(
			'gcow-product-suggestion',
			GCOW_PLUGIN_URL . 'assets/js/product-suggestion.js',
			[],
			GCOW_VERSION,
			true
		);
	}

	public function render_slider(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		global $product;
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$suggested_ids = $this->get_ai_suggestions( $product->get_id() );
		if ( count( $suggested_ids ) < 2 ) {
			return;
		}

		$products = array_values( array_filter( array_map( 'wc_get_product', $suggested_ids ) ) );
		if ( count( $products ) < 2 ) {
			return;
		}
		?>
		<div class="gcow-ps-wrap">
			<div class="gcow-ps-header">
				<h4 class="gcow-ps-title"><?php esc_html_e( 'You Might Also Like', 'giant-checkout-offers-for-woocommerce' ); ?></h4>
				<div class="gcow-ps-arrows">
					<button class="gcow-ps-prev" aria-label="<?php esc_attr_e( 'Previous', 'giant-checkout-offers-for-woocommerce' ); ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<button class="gcow-ps-next" aria-label="<?php esc_attr_e( 'Next', 'giant-checkout-offers-for-woocommerce' ); ?>">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>
			</div>
			<div class="gcow-ps-viewport">
				<div class="gcow-ps-track">
					<?php foreach ( $products as $suggested ) : ?>
					<div class="gcow-ps-item">
						<a href="<?php echo esc_url( get_permalink( $suggested->get_id() ) ); ?>" class="gcow-ps-card">
							<div class="gcow-ps-img-wrap">
								<?php echo wp_kses_post( $suggested->get_image( 'woocommerce_thumbnail' ) ); ?>
							</div>
							<div class="gcow-ps-info">
								<span class="gcow-ps-name"><?php echo esc_html( $suggested->get_name() ); ?></span>
								<span class="gcow-ps-price"><?php echo wp_kses_post( $suggested->get_price_html() ); ?></span>
							</div>
						</a>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function is_enabled(): bool {
		$settings = get_option( 'gcow_settings', [] );
		return ! empty( $settings['aiProductSuggestion'] );
	}

	/**
	 * Returns AI-suggested product IDs for the given product.
	 *
	 * @param int $product_id
	 * @return int[]
	 */
	private function get_ai_suggestions( int $product_id ): array {
		$knowledge   = get_option( 'gcow_ai_knowledge', [] );
		$suggestions = $knowledge['suggestions'] ?? [];
		$key         = (string) $product_id;

		if ( empty( $suggestions[ $key ] ) || ! is_array( $suggestions[ $key ] ) ) {
			return [];
		}

		return array_values( array_filter( array_map( 'absint', $suggestions[ $key ] ) ) );
	}
}
