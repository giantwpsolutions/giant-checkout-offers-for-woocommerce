<?php
defined( 'ABSPATH' ) || exit;
/**
 * Hero Order Bump Template (Gradient)
 *
 * @var array $bump_data
 * @var array $styles
 */
?>
<div class="gcow-bump-container" data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>" style="margin: 20px 0;">
	<div style="
		background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#6366f1' ); ?>;
		border-radius: <?php echo esc_attr( $styles['borderRadius'] ?? 8 ); ?>px;
		padding: <?php echo esc_attr( $styles['padding'] ?? 16 ); ?>px;
		position: relative;
		overflow: hidden;
		box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		text-align: center;
	">
		<div style="position: absolute; top: -32px; right: -32px; width: 80px; height: 80px; background-color: rgba(255,255,255,0.1); border-radius: 50%;"></div>
		<!-- Header label -->
		<?php if ( ! empty( $bump_data['title'] ) ) : ?>
			<span style="color: rgba(255,255,255,0.85); font-size: 10px; text-transform: uppercase; letter-spacing: 1px;"><?php echo esc_html( $bump_data['title'] ); ?></span>
		<?php endif; ?>
		<!-- Image -->
		<div style="width: 64px; height: 64px; border-radius: 10px; margin: 8px auto; overflow: hidden; background-color: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
			<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
				<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;" />
			<?php else : ?>
				<svg style="width: 32px; height: 32px; color: rgba(255,255,255,0.7);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
			<?php endif; ?>
		</div>
		<!-- Name -->
		<h4 style="color: #ffffff; font-size: 16px; font-weight: 700; margin: 0 0 4px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
		<?php if ( ! empty( $bump_data['description'] ) ) : ?>
			<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#c7d2fe' ); ?>; font-size: 12px; margin: 0 0 8px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
		<?php endif; ?>
		<!-- Price -->
		<div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px;">
			<span style="
				color: <?php echo esc_attr( $styles['priceColor'] ?? '#ffffff' ); ?>;
				font-size: 20px;
				font-weight: 700;
			"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
			<?php if ( $bump_data['discount_value'] > 0 ) : ?>
			<span style="font-size: 12px; text-decoration: line-through; opacity: 0.6; color: #ffffff;"><?php echo wp_kses_post( $bump_data['regular_price'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
		<!-- CTA Button -->
		<label style="display: flex; align-items: center; justify-content: center; gap: 8px; background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#ffffff' ); ?>; border: 2px solid rgba(255,255,255,0.8); border-radius: 6px; padding: 10px 16px; cursor: pointer; color: <?php echo esc_attr( $styles['buttonTextColor'] ?? '#ffffff' ); ?>;">
			<input type="checkbox" class="gcow-bump-checkbox"
				data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
				data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
				data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
				style="width: 18px; height: 18px; cursor: pointer; accent-color: #ffffff;"
			/>
			<span style="color: #ffffff; font-size: 13px; font-weight: 700;"><?php echo esc_html( $bump_data['button_text'] ); ?></span>
		</label>
	</div>
</div>
