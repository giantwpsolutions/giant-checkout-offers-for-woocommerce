<?php
defined( 'ABSPATH' ) || exit;
/**
 * Split Order Bump Template
 *
 * @var array $bump_data
 * @var array $styles
 */
?>
<div class="gcow-bump-container" data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>" style="margin: 20px 0;">
	<div style="
		display: flex;
		background-color: <?php echo esc_attr( $styles['backgroundColor'] ?? '#ffffff' ); ?>;
		border: <?php echo esc_attr( $styles['borderWidth'] ?? 1 ); ?>px <?php echo esc_attr( $styles['borderStyle'] ?? 'solid' ); ?> <?php echo esc_attr( $styles['borderColor'] ?? '#e5e7eb' ); ?>;
		border-radius: <?php echo esc_attr( $styles['borderRadius'] ?? 8 ); ?>px;
		overflow: hidden;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	">
		<!-- Left Panel (Image) -->
		<div style="width: 40%; padding: 12px; display: flex; align-items: center; justify-content: center; background-color: <?php echo esc_attr( $styles['buttonColor'] ?? '#0d9488' ); ?>;">
			<?php if ( ! empty( $bump_data['image_url'] ) ) : ?>
				<img src="<?php echo esc_url( $bump_data['image_url'] ); ?>" alt="<?php echo esc_attr( $bump_data['title'] ); ?>" style="width: 100%; height: 80px; object-fit: cover; border-radius: 6px;" />
			<?php else : ?>
				<div style="width: 48px; height: 48px; background-color: rgba(255,255,255,0.3); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
					<svg style="width: 28px; height: 28px; color: #ffffff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
				</div>
			<?php endif; ?>
		</div>
		<!-- Right Panel (Info) -->
		<div style="width: 60%; padding: 12px;">
			<!-- Stars -->
			<div style="display: flex; gap: 2px; margin-bottom: 4px;">
				<?php for ( $gcow_i = 0; $gcow_i < 5; $gcow_i++ ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<svg style="width: 12px; height: 12px; color: <?php echo esc_attr( $styles['starColor'] ?? '#facc15' ); ?>;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
				<?php endfor; ?>
			</div>
			<h4 style="color: <?php echo esc_attr( $styles['textColor'] ?? '#1f2937' ); ?>; font-size: 13px; font-weight: 700; margin: 0 0 2px;"><?php echo esc_html( $bump_data['title'] ); ?></h4>
			<?php if ( ! empty( $bump_data['description'] ) ) : ?>
				<p style="color: <?php echo esc_attr( $styles['descriptionColor'] ?? '#6b7280' ); ?>; font-size: 11px; margin: 0 0 6px;"><?php echo esc_html( $bump_data['description'] ); ?></p>
			<?php endif; ?>
		<?php include __DIR__ . '/parts/variation-selectors.php'; ?>
			<div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px;">
				<span style="
					color: <?php echo esc_attr( $styles['priceColor'] ?? '#0d9488' ); ?>;
					font-size: 16px;
					font-weight: 700;
				"><?php echo wp_kses_post( $bump_data['discounted_price'] ); ?></span>
				<!-- Toggle Switch -->
				<label style="position: relative; width: 44px; height: 24px; cursor: pointer; display: inline-block;">
					<input type="checkbox" class="gcow-bump-checkbox"
						data-product-id="<?php echo esc_attr( $bump_data['product_id'] ); ?>"
						data-variation-id="<?php echo esc_attr( $bump_data['variation_id'] ); ?>"
					data-variation-attrs="<?php echo esc_attr( wp_json_encode( $bump_data['variation_attributes'] ) ); ?>"
						data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
						style="opacity: 0; width: 0; height: 0;"
					/>
					<span style="
						position: absolute;
						top: 0; left: 0; right: 0; bottom: 0;
						background-color: <?php echo esc_attr( $styles['toggleColor'] ?? '#14b8a6' ); ?>;
						border-radius: 999px;
						transition: 0.3s;
					">
						<span style="
							position: absolute;
							width: 18px; height: 18px;
							background-color: #ffffff;
							border-radius: 50%;
							top: 3px;
							right: 4px;
							transition: 0.3s;
						"></span>
					</span>
				</label>
			</div>
		</div>
	</div>
</div>
