<?php
defined( 'ABSPATH' ) || exit;
/**
 * Timeline Order Bump Template
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
		padding: <?php echo esc_attr( $styles['padding'] ?? 12 ); ?>px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	">
		<!-- Title & Name -->
		<div style="text-align: center; margin-bottom: 10px;">
			<?php if ( ! empty( $bump_data['title'] ) ) : ?>
				<span style="color: <?php echo esc_attr( $styles['accentColor'] ?? '#8b5cf6' ); ?>; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;"><?php echo esc_html( $bump_data['title'] ); ?></span>
			<?php endif; ?>
			<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 14px; font-weight: 700; margin: 2px 0 0;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
			<?php if ( ! empty( $bump_data['description'] ) ) : ?>
				<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 12px; margin: 2px 0 0;"><?php echo esc_html( $bump_data['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<!-- Timeline Steps -->
		<div style="display: flex; align-items: center; gap: 4px; margin-bottom: 10px;">
			<div style="width: 22px; height: 22px; border-radius: 50%; background-color: <?php echo esc_attr( $styles['accentColor'] ?? '#8b5cf6' ); ?>; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0;">1</div>
			<div style="flex: 1; height: 2px; border-radius: 2px; background-color: <?php echo esc_attr( ( $styles['accentColor'] ?? '#8b5cf6' ) ); ?>60;"></div>
			<div style="width: 22px; height: 22px; border-radius: 50%; background-color: <?php echo esc_attr( $styles['accentColor'] ?? '#8b5cf6' ); ?>; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0;">2</div>
			<div style="flex: 1; height: 2px; border-radius: 2px; background-color: #e5e7eb;"></div>
			<div style="width: 22px; height: 22px; border-radius: 50%; background-color: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0;">3</div>
		</div>
		<!-- Product Card -->
		<div style="padding: 10px; border-radius: 6px; border: 1px solid <?php echo esc_attr( ( $styles['accentColor'] ?? '#8b5cf6' ) ); ?>25; background-color: <?php echo esc_attr( ( $styles['accentColor'] ?? '#8b5cf6' ) ); ?>10;">
			<div style="display: flex; align-items: center; justify-content: space-between;">
				<div style="display: flex; align-items: center; gap: 8px;">
					<div style="width: 32px; height: 32px; border-radius: 6px; overflow: hidden; background-color: <?php echo esc_attr( ( $styles['accentColor'] ?? '#8b5cf6' ) ); ?>20; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
						<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
							<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
						<?php else : ?>
							<svg style="width: 16px; height: 16px; color: <?php echo esc_attr( $styles['accentColor'] ?? '#8b5cf6' ); ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
						<?php endif; ?>
					</div>
					<div>
						<p style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 12px; font-weight: 600; margin: 0;"><?php echo esc_html( $bump_data['title'] ); ?></p>
					</div>
				</div>
				<span style="color: <?php echo esc_attr( $styles['priceColor'] ?? '#7c3aed' ); ?>; font-size: 15px; font-weight: 700;"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
			</div>
		</div>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
		<!-- CTA -->
		<label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 8px 12px; background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#8b5cf6' ); ?>; border-radius: 6px; cursor: pointer;">
			<input type="checkbox" class="gcow-bump-checkbox"
				data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
				data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
				data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
				style="width: 18px; height: 18px; cursor: pointer; accent-color: #ffffff; flex-shrink: 0;"
			/>
			<span style="color: <?php echo esc_attr( $styles['buttonTextColor'] ?? '#ffffff' ); ?>; font-size: 13px; font-weight: 600;"><?php echo esc_html( $bump_data['button_text'] ); ?></span>
		</label>
	</div>
</div>
