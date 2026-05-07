<?php
defined( 'ABSPATH' ) || exit;
/**
 * Social Proof Order Bump Template
 *
 * @var array $bump_data
 * @var array $styles
 */
?>
<div class="gcow-bump-container" data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>" style="margin: 20px 0;">
	<div style="
		background-color: <?php echo esc_attr( $styles['backgroundColor'] ?? '#ffffff' ); ?>;
		border: <?php echo esc_attr( $styles['borderWidth'] ?? 1 ); ?>px <?php echo esc_attr( $styles['borderStyle'] ?? 'solid' ); ?> <?php echo esc_attr( $styles['borderColor'] ?? '#e5e7eb' ); ?>;
		border-radius: <?php echo esc_attr( $styles['borderRadius'] ?? 8 ); ?>px;
		overflow: hidden;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	">
		<!-- Social Header -->
		<div style="padding: 8px 12px; background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#10b981' ); ?>;">
			<div style="display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
				<div style="display: flex; align-items: center; gap: 6px;">
					<div style="display: flex; margin-right: 2px;">
						<div style="width: 16px; height: 16px; border-radius: 50%; background-color: rgba(255,255,255,0.3); border: 1px solid #ffffff; margin-right: -6px;"></div>
						<div style="width: 16px; height: 16px; border-radius: 50%; background-color: rgba(255,255,255,0.3); border: 1px solid #ffffff; margin-right: -6px;"></div>
						<div style="width: 16px; height: 16px; border-radius: 50%; background-color: rgba(255,255,255,0.3); border: 1px solid #ffffff;"></div>
					</div>
					<span style="font-size: 10px; font-weight: 500; margin-left: 8px;"><?php esc_html_e( '2,847 bought today', 'giant-checkout-offers-for-woocommerce' ); ?></span>
				</div>
				<div style="display: flex; align-items: center; gap: 2px;">
					<svg style="width: 12px; height: 12px;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
					<span style="font-size: 10px; font-weight: 700;">4.9</span>
				</div>
			</div>
		</div>
		<!-- Body -->
		<div style="padding: <?php echo esc_attr( $styles['padding'] ?? 10 ); ?>px;">
			<div style="display: flex; gap: 10px;">
				<div style="width: 48px; height: 48px; flex-shrink: 0; border-radius: 6px; overflow: hidden; background-color: <?php echo esc_attr( ( $styles['buttonColor'] ?? '#10b981' ) ); ?>20; display: flex; align-items: center; justify-content: center;">
					<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
						<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
					<?php else : ?>
						<svg style="width: 24px; height: 24px; color: <?php echo esc_attr( $styles['buttonColor'] ?? '#10b981' ); ?>80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
					<?php endif; ?>
				</div>
				<div style="flex: 1;">
					<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 700; margin: 0 0 2px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
					<?php if ( ! empty( $bump_data['description'] ) ) : ?>
						<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 11px; margin: 0 0 4px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
					<?php endif; ?>
					<div style="display: flex; align-items: center; gap: 6px;">
						<span style="color: <?php echo esc_attr( $styles['priceColor'] ?? '#059669' ); ?>; font-size: 16px; font-weight: 700;"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
						<span style="color: #9ca3af; font-size: 13px; text-decoration: line-through;"><?php echo wp_kses_post( $bump_data['regular_price'] ); ?></span>
					</div>
				</div>
			</div>
			<!-- Testimonial -->
			<div style="margin-top: 8px; padding: 8px 10px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
				<p style="font-size: 11px; color: #4b5563; margin: 0; font-style: italic;"><?php esc_html_e( '"Best purchase I made!" - Sarah M.', 'giant-checkout-offers-for-woocommerce' ); ?></p>
			</div>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
			<!-- CTA -->
			<label style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding: 8px 10px; background-color: <?php echo esc_attr( $styles['ctaBgColor'] ?? '#ecfdf5' ); ?>; border: 1px solid <?php echo esc_attr( $styles['ctaBorderColor'] ?? '#a7f3d0' ); ?>; border-radius: 6px; cursor: pointer;">
				<span style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 12px; font-weight: 600;"><?php echo esc_html( $bump_data['button_text'] ); ?></span>
				<input type="checkbox" class="gcow-bump-checkbox"
					data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
					data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
					data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
					style="width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;"
				/>
			</label>
		</div>
	</div>
</div>
