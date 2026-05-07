<?php
defined( 'ABSPATH' ) || exit;
/**
 * Ribbon Order Bump Template
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
		position: relative;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	">
		<!-- Corner Ribbon -->
		<?php if ( $bump_data['discount_value'] > 0 ) : ?>
			<div style="position: absolute; top: 0; right: 0; width: 64px; height: 64px; overflow: hidden;">
				<div style="
					position: absolute;
					transform: rotate(45deg);
					background-color: <?php echo esc_attr( $styles['ribbonColor'] ?? '#ef4444' ); ?>;
					color: #ffffff;
					font-size: 10px;
					font-weight: 700;
					text-align: center;
					padding: 2px 0;
					right: -20px;
					top: 12px;
					width: 80px;
					box-shadow: 0 1px 3px rgba(0,0,0,0.2);
				">
				<?php echo wp_kses_post( $bump_data['saved_price'] ); ?> OFF
				</div>
			</div>
		<?php endif; ?>
		<div style="display: flex; gap: 10px; padding: <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px;">
			<!-- Image -->
			<div style="width: 52px; height: 52px; flex-shrink: 0; border-radius: 8px; overflow: hidden; background-color: <?php echo esc_attr( ( $styles['accentColor'] ?? '#d97706' ) ); ?>20; display: flex; align-items: center; justify-content: center;">
				<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
				<?php else : ?>
					<svg style="width: 26px; height: 26px; color: <?php echo esc_attr( $styles['accentColor'] ?? '#d97706' ); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
				<?php endif; ?>
			</div>
			<!-- Info -->
			<div style="flex: 1; min-width: 0;">
				<?php if ( ! empty( $bump_data['title'] ) ) : ?>
					<span style="color: <?php echo esc_attr( $styles['accentColor'] ?? '#d97706' ); ?>; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo esc_html( $bump_data['title'] ); ?></span>
				<?php endif; ?>
				<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 700; margin: 0 0 2px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
				<?php if ( ! empty( $bump_data['description'] ) ) : ?>
					<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 11px; margin: 0 0 6px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
				<?php endif; ?>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
			<div style="display: flex; align-items: center; gap: 8px;">
					<span style="color: <?php echo esc_attr( $styles['priceColor'] ?? '#111827' ); ?>; font-size: 16px; font-weight: 700;"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
					<span style="color: #9ca3af; font-size: 12px; text-decoration: line-through;"><?php echo wp_kses_post( $bump_data['regular_price'] ); ?></span>
					<label style="display: inline-flex; align-items: center; gap: 5px; background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#3b82f6' ); ?>; color: <?php echo esc_attr( $styles['buttonTextColor'] ?? '#ffffff' ); ?>; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 4px; cursor: pointer;">
						<input type="checkbox" class="gcow-bump-checkbox"
							data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
							data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
							data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
							style="width: 14px; height: 14px; cursor: pointer;"
						/>
						<?php echo esc_html( $bump_data['button_text'] ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>
</div>
