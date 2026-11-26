<?php
/**
 * Order Bump Elementor Widget — Render View.
 *
 * Included by Main::render() with $settings already set.
 *
 * @package GiantCheckoutOffers\Frontend\Elementor\Widgets\OrderBump
 * @var array $settings Elementor settings for this widget instance.
 */

defined( 'ABSPATH' ) || exit;

$template       = $settings['template']            ?? 'standard';
$title          = $settings['bump_title']          ?? '';
$description    = $settings['bump_description']    ?? '';
$button_text    = $settings['bump_button_text']    ?? esc_html__( 'Yes, Add to Order!', 'giant-checkout-offers-for-woocommerce' );
$image_url      = $settings['bump_image']['url']   ?? '';
$regular_price  = $settings['bump_regular_price']  ?? '$29.00';
$disc_price     = $settings['bump_discounted_price'] ?? '$19.00';
$saved_price    = $settings['bump_saved_price']    ?? '$10.00';
$show_discount  = ( $settings['show_discount'] ?? 'yes' ) === 'yes';

// Shared placeholder SVG (used when no image is provided).
$placeholder_svg = '<svg style="width:28px;height:28px;color:#d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
	. '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" '
	. 'd="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01'
	. 'M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';

$img_html = $image_url
	? '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" class="gcow-el-product-img" />'
	: $placeholder_svg;

// ── Template: Standard ────────────────────────────────────────────────────
if ( $template === 'standard' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-standard">
	<div class="gcow-el-card">
		<div class="gcow-el-header">
			<p class="gcow-el-header-title"><?php echo esc_html( $title ); ?></p>
		</div>
		<div class="gcow-el-body">
			<div class="gcow-el-img-wrap">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-info">
				<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="gcow-el-price-row">
					<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
					<?php if ( $show_discount ) : ?>
						<span class="gcow-el-regular-price"><?php echo esc_html( $regular_price ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="gcow-el-cta-section">
			<label class="gcow-el-cta-label gcow-el-cta-wrap">
				<input type="checkbox" class="gcow-el-checkbox" disabled />
				<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
			</label>
		</div>
	</div>
</div>

<?php // ── Template: Compact ──────────────────────────────────────────────────
elseif ( $template === 'compact' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-compact">
	<div class="gcow-el-card">
		<div class="gcow-el-compact-row">
			<input type="checkbox" class="gcow-el-checkbox gcow-el-compact-check" disabled />
			<div class="gcow-el-compact-img-wrap">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-compact-info">
				<div class="gcow-el-compact-top">
					<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
					<div class="gcow-el-compact-price-wrap">
						<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
						<?php if ( $show_discount ) : ?>
							<span class="gcow-el-save-badge"><?php
								/* translators: %s: saved amount */
								echo esc_html( sprintf( __( 'Save %s', 'giant-checkout-offers-for-woocommerce' ), $saved_price ) );
							?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php // ── Template: Social ───────────────────────────────────────────────────
elseif ( $template === 'social' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-social">
	<div class="gcow-el-card">
		<div class="gcow-el-header gcow-el-social-header">
			<div class="gcow-el-social-avatars">
				<span class="gcow-el-avatar"></span>
				<span class="gcow-el-avatar"></span>
				<span class="gcow-el-avatar"></span>
			</div>
			<span class="gcow-el-social-count"><?php esc_html_e( '2,847 bought today', 'giant-checkout-offers-for-woocommerce' ); ?></span>
			<div class="gcow-el-social-rating">
				<svg style="width:12px;height:12px;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
				<span>4.9</span>
			</div>
		</div>
		<div class="gcow-el-body">
			<div class="gcow-el-img-wrap gcow-el-img-wrap--sm">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-info">
				<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="gcow-el-price-row">
					<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
					<?php if ( $show_discount ) : ?>
						<span class="gcow-el-regular-price"><?php echo esc_html( $regular_price ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="gcow-el-social-quote">
			<p><?php esc_html_e( '"Best purchase I made!" - Sarah M.', 'giant-checkout-offers-for-woocommerce' ); ?></p>
		</div>
		<label class="gcow-el-cta-label gcow-el-cta-wrap gcow-el-social-cta">
			<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
			<input type="checkbox" class="gcow-el-checkbox" disabled />
		</label>
	</div>
</div>

<?php // ── Template: Split ────────────────────────────────────────────────────
elseif ( $template === 'split' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-split">
	<div class="gcow-el-card gcow-el-split-card">
		<div class="gcow-el-split-left">
			<?php echo wp_kses_post( $img_html ); ?>
		</div>
		<div class="gcow-el-split-right">
			<div class="gcow-el-stars">
				<?php for ( $gcow_i = 0; $gcow_i < 5; $gcow_i++ ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<svg style="width:12px;height:12px;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
				<?php endfor; ?>
			</div>
			<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
			<?php if ( $description ) : ?>
				<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<div class="gcow-el-split-footer">
				<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
				<label class="gcow-el-toggle-wrap">
					<input type="checkbox" class="gcow-el-checkbox gcow-el-toggle-input" disabled />
					<span class="gcow-el-toggle-track">
						<span class="gcow-el-toggle-knob"></span>
					</span>
				</label>
			</div>
		</div>
	</div>
</div>

<?php // ── Template: Hero ─────────────────────────────────────────────────────
elseif ( $template === 'hero' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-hero">
	<div class="gcow-el-card gcow-el-hero-card">
		<div class="gcow-el-hero-deco"></div>
		<span class="gcow-el-hero-eyebrow"><?php echo esc_html( $title ); ?></span>
		<div class="gcow-el-hero-img-wrap">
			<?php echo wp_kses_post( $img_html ); ?>
		</div>
		<h4 class="gcow-el-title gcow-el-hero-title"><?php echo esc_html( $title ); ?></h4>
		<?php if ( $description ) : ?>
			<p class="gcow-el-description gcow-el-hero-desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
		<div class="gcow-el-hero-price-row">
			<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
			<?php if ( $show_discount ) : ?>
				<span class="gcow-el-hero-regular-price"><?php echo esc_html( $regular_price ); ?></span>
			<?php endif; ?>
		</div>
		<label class="gcow-el-cta-label gcow-el-hero-cta">
			<input type="checkbox" class="gcow-el-checkbox" disabled />
			<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
		</label>
	</div>
</div>

<?php // ── Template: Ribbon ───────────────────────────────────────────────────
elseif ( $template === 'ribbon' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-ribbon">
	<div class="gcow-el-card">
		<?php if ( $show_discount ) : ?>
			<div class="gcow-el-ribbon-corner">
				<div class="gcow-el-ribbon-flag"><?php echo esc_html( $saved_price ); ?> OFF</div>
			</div>
		<?php endif; ?>
		<div class="gcow-el-body">
			<div class="gcow-el-ribbon-img-wrap">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-info">
				<span class="gcow-el-accent gcow-el-ribbon-label"><?php echo esc_html( $title ); ?></span>
				<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="gcow-el-ribbon-price-row">
					<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
					<?php if ( $show_discount ) : ?>
						<span class="gcow-el-regular-price"><?php echo esc_html( $regular_price ); ?></span>
					<?php endif; ?>
					<label class="gcow-el-cta-label gcow-el-ribbon-cta">
						<input type="checkbox" class="gcow-el-checkbox" disabled />
						<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
					</label>
				</div>
			</div>
		</div>
	</div>
</div>

<?php // ── Template: Floating ─────────────────────────────────────────────────
elseif ( $template === 'floating' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-floating">
	<?php if ( $show_discount ) : ?>
		<div class="gcow-el-floating-badge-wrap">
			<span class="gcow-el-badge"><?php echo esc_html( $saved_price ); ?> OFF</span>
		</div>
	<?php endif; ?>
	<div class="gcow-el-card">
		<div class="gcow-el-body">
			<div class="gcow-el-floating-img-wrap">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-info">
				<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="gcow-el-price-row">
					<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
					<?php if ( $show_discount ) : ?>
						<span class="gcow-el-regular-price"><?php echo esc_html( $regular_price ); ?></span>
						<span class="gcow-el-floating-inline-badge"><?php echo esc_html( $saved_price ); ?> OFF</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<label class="gcow-el-cta-label gcow-el-cta-wrap gcow-el-floating-cta">
			<input type="checkbox" class="gcow-el-checkbox" disabled />
			<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
		</label>
	</div>
</div>

<?php // ── Template: Timeline ─────────────────────────────────────────────────
elseif ( $template === 'timeline' ) : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-timeline">
	<div class="gcow-el-card">
		<div class="gcow-el-timeline-header">
			<span class="gcow-el-timeline-label"><?php echo esc_html( $title ); ?></span>
			<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
			<?php if ( $description ) : ?>
				<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<div class="gcow-el-timeline-steps">
			<div class="gcow-el-timeline-step">1</div>
			<div class="gcow-el-timeline-line gcow-el-timeline-line--active"></div>
			<div class="gcow-el-timeline-step">2</div>
			<div class="gcow-el-timeline-line gcow-el-timeline-line--inactive"></div>
			<div class="gcow-el-timeline-step gcow-el-timeline-step--inactive">3</div>
		</div>
		<div class="gcow-el-timeline-product">
			<div class="gcow-el-timeline-product-left">
				<div class="gcow-el-timeline-thumb-wrap">
					<?php echo wp_kses_post( $img_html ); ?>
				</div>
				<p class="gcow-el-title gcow-el-timeline-product-name"><?php echo esc_html( $title ); ?></p>
			</div>
			<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
		</div>
		<label class="gcow-el-cta-label gcow-el-cta-bg gcow-el-timeline-cta">
			<input type="checkbox" class="gcow-el-checkbox" disabled />
			<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
		</label>
	</div>
</div>

<?php // ── Fallback: re-use standard ───────────────────────────────────────────
else : ?>
<div class="gcow-bump-container gcow-el-wrap gcow-el-standard">
	<div class="gcow-el-card">
		<div class="gcow-el-header">
			<p class="gcow-el-header-title"><?php echo esc_html( $title ); ?></p>
		</div>
		<div class="gcow-el-body">
			<div class="gcow-el-img-wrap">
				<?php echo wp_kses_post( $img_html ); ?>
			</div>
			<div class="gcow-el-info">
				<h4 class="gcow-el-title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $description ) : ?>
					<p class="gcow-el-description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="gcow-el-price-row">
					<span class="gcow-el-price gcow-el-discounted-price"><?php echo esc_html( $disc_price ); ?></span>
					<?php if ( $show_discount ) : ?>
						<span class="gcow-el-regular-price"><?php echo esc_html( $regular_price ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="gcow-el-cta-section">
			<label class="gcow-el-cta-label gcow-el-cta-wrap">
				<input type="checkbox" class="gcow-el-checkbox" disabled />
				<span class="gcow-el-cta-text"><?php echo esc_html( $button_text ); ?></span>
			</label>
		</div>
	</div>
</div>
<?php endif; ?>
