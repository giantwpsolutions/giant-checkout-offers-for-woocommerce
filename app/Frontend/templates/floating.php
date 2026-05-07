<?php
defined( 'ABSPATH' ) || exit;
/**
 * Floating Order Bump Template (Badge with card)
 *
 * @var array $bump_data
 * @var array $styles
 */
?>
<div class="gcow-bump-container" data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>" style="margin: 20px 0; position: relative; padding-top: 14px;">
	<!-- Discount Badge -->
	<?php if ( $bump_data['discount_value'] > 0 ) : ?>
		<div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); z-index: 1;">
			<span style="
				background-color: <?php echo esc_attr( $styles['badgeColor'] ?? '#f43f5e' ); ?>;
				color: <?php echo esc_attr( $styles['badgeTextColor'] ?? '#ffffff' ); ?>;
				font-size: 10px;
				font-weight: 700;
				padding: 3px 10px;
				border-radius: 999px;
				white-space: nowrap;
				box-shadow: 0 2px 4px rgba(0,0,0,0.2);
			">
				<?php echo wp_kses_post( $bump_data['saved_price'] ); ?> OFF
			</span>
		</div>
	<?php endif; ?>
	<div style="
		background-color: <?php echo esc_attr( $styles['backgroundColor'] ?? '#ffffff' ); ?>;
		border: <?php echo esc_attr( $styles['borderWidth'] ?? 1 ); ?>px <?php echo esc_attr( $styles['borderStyle'] ?? 'solid' ); ?> <?php echo esc_attr( $styles['borderColor'] ?? '#e5e7eb' ); ?>;
		border-radius: <?php echo esc_attr( $styles['borderRadius'] ?? 12 ); ?>px;
		padding: <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px;
		box-shadow: 0 4px 12px rgba(0,0,0,0.1);
	">
		<div style="display: flex; gap: 10px;">
			<!-- Image -->
			<div style="width: 52px; height: 52px; flex-shrink: 0; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background-color: <?php echo esc_attr( ( $styles['badgeColor'] ?? '#f43f5e' ) ); ?>20;">
				<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
				<?php else : ?>
					<svg style="width: 26px; height: 26px; color: <?php echo esc_attr( $styles['badgeColor'] ?? '#f43f5e' ); ?>80;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
				<?php endif; ?>
			</div>
			<!-- Info -->
			<div style="flex: 1;">
				<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 700; margin: 0 0 2px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
				<?php if ( ! empty( $bump_data['description'] ) ) : ?>
					<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 11px; margin: 0 0 4px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
				<?php endif; ?>
				<div style="display: flex; align-items: baseline; gap: 6px;">
					<span style="
						color: <?php echo esc_attr( $styles['priceColor'] ?? '#f43f5e' ); ?>;
						font-size: 16px;
						font-weight: 700;
					"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
					<span style="color: #9ca3af; font-size: 12px; text-decoration: line-through;"><?php echo wp_kses_post( $bump_data['regular_price'] ); ?></span>
					<?php if ( $bump_data['discount_value'] > 0 ) : ?>
						<span style="
							background-color: <?php echo esc_attr( ( $styles['badgeColor'] ?? '#f43f5e' ) ); ?>20;
							color: <?php echo esc_attr( $styles['badgeColor'] ?? '#f43f5e' ); ?>;
							font-size: 10px;
							padding: 1px 5px;
							border-radius: 4px;
							font-weight: 600;
						"><?php echo wp_kses_post( $bump_data['saved_price'] ); ?> OFF</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
		<!-- CTA -->
		<label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 8px 12px; background-color: <?php echo esc_attr( $styles['ctaBgColor'] ?? '#fef2f2' ); ?>; border: 1px solid <?php echo esc_attr( $styles['ctaBorderColor'] ?? '#fecaca' ); ?>; border-radius: 6px; cursor: pointer;">
			<input type="checkbox" class="gcow-bump-checkbox"
				data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
				data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
				data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
				style="width: 18px; height: 18px; cursor: pointer;"
			/>
			<span style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 600;"><?php echo esc_html( $bump_data['button_text'] ); ?></span>
		</label>
	</div>
</div>
