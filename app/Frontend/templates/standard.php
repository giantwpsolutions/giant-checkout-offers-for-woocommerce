<?php
defined( 'ABSPATH' ) || exit;
/**
 * Standard Order Bump Template
 *
 * @var array $bump_data
 * @var array $styles
 */
?>
<div class="gcow-bump-container" data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>" style="margin: 20px 0;">
	<div style="
		background-color: <?php echo esc_attr( $styles['backgroundColor'] ?? '#ffffff' ); ?>;
		border: <?php echo esc_attr( $styles['borderWidth'] ?? 1 ); ?>px <?php echo esc_attr( $styles['borderStyle'] ?? 'dashed' ); ?> <?php echo esc_attr( $styles['borderColor'] ?? '#e5e7eb' ); ?>;
		border-radius: <?php echo esc_attr( $styles['borderRadius'] ?? 8 ); ?>px;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	">
		<!-- Header -->
		<div style="background-color: <?php echo esc_attr( $styles['headerBgColor'] ?? $styles['buttonColor'] ?? '#3b82f6' ); ?>; padding: 8px 16px;">
			<p style="color: <?php echo esc_attr( $styles['headerTextColor'] ?? '#ffffff' ); ?>; font-size: 13px; font-weight: 600; margin: 0;"><?php echo esc_html( $bump_data['title'] ); ?></p>
		</div>
		<!-- Body -->
		<div style="display: flex; gap: 12px; padding: <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px;">
			<div style="width: 64px; height: 64px; flex-shrink: 0; border-radius: 6px; overflow: hidden; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
				<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
					<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
				<?php else : ?>
					<svg style="width: 28px; height: 28px; color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
				<?php endif; ?>
			</div>
			<div style="flex: 1; min-width: 0;">
				<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 14px; font-weight: 600; margin: 0 0 4px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
				<?php if ( ! empty( $bump_data['description'] ) ) : ?>
					<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 12px; margin: 0 0 6px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
				<?php endif; ?>
				<div style="display: flex; align-items: center; gap: 8px;">
					<span style="color: <?php echo esc_attr( $styles['priceColor'] ?? '#16a34a' ); ?>; font-size: 15px; font-weight: 700;"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
					<?php if ( $bump_data['discount_value'] > 0 ) : ?>
						<span style="color: #9ca3af; font-size: 12px; text-decoration: line-through;"><?php echo wp_kses_post( $bump_data['regular_price'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
		<!-- CTA -->
		<div style="padding: 0 <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px;">
			<label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 6px; border: 1px solid <?php echo esc_attr( $styles['ctaBorderColor'] ?? '#bfdbfe' ); ?>; background-color: <?php echo esc_attr( $styles['ctaBgColor'] ?? '#eff6ff' ); ?>; cursor: pointer;">
				<input type="checkbox" class="gcow-bump-checkbox"
					data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
					data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
					data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
					style="width: 18px; height: 18px; cursor: pointer; accent-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#3b82f6' ); ?>; flex-shrink: 0;"
				/>
				<span style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 600;"><?php echo esc_html( $bump_data['button_text'] ); ?></span>
			</label>
		</div>
	</div>
</div>
