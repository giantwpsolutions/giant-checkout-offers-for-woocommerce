<?php
defined( 'ABSPATH' ) || exit;
/**
 * Variation / Attribute Selectors partial.
 *
 * Required variables (inherited from parent template scope):
 *   @var array $bump_data
 *   @var array $styles
 */

if ( empty( $bump_data['attribute_selectors'] ) ) {
	return;
}

$gcow_settings  = get_option( 'gcow_settings', [] );
$use_swatches   = $gcow_settings['variationSwatches'] ?? true;
$gcow_btn_color = $styles['buttonColor'] ?? '#3b82f6';
$gcow_padding_x = absint( $styles['padding'] ?? 12 );

// Common CSS color names → hex.
// Checked first when a swatch plugin has not stored term meta.
$gcow_color_map = [
	'red'          => '#ef4444',
	'blue'         => '#3b82f6',
	'green'        => '#22c55e',
	'yellow'       => '#eab308',
	'orange'       => '#f97316',
	'purple'       => '#a855f7',
	'pink'         => '#ec4899',
	'white'        => '#ffffff',
	'black'        => '#111827',
	'gray'         => '#9ca3af',
	'grey'         => '#9ca3af',
	'brown'        => '#92400e',
	'navy'         => '#1e3a5f',
	'teal'         => '#14b8a6',
	'cyan'         => '#06b6d4',
	'magenta'      => '#d946ef',
	'violet'       => '#7c3aed',
	'indigo'       => '#6366f1',
	'lime'         => '#84cc16',
	'maroon'       => '#7f1d1d',
	'olive'        => '#65a30d',
	'coral'        => '#ff7057',
	'salmon'       => '#fca5a5',
	'gold'         => '#f59e0b',
	'silver'       => '#d1d5db',
	'beige'        => '#f5f0e8',
	'ivory'        => '#fefce8',
	'cream'        => '#fef3c7',
	'khaki'        => '#c3b080',
	'turquoise'    => '#2dd4bf',
	'lavender'     => '#c4b5fd',
	'mint'         => '#6ee7b7',
	'rose'         => '#fb7185',
	'peach'        => '#fdba74',
	'chocolate'    => '#7b3f00',
	'tan'          => '#d4a853',
	'sky'          => '#38bdf8',
	'sky blue'     => '#38bdf8',
	'emerald'      => '#10b981',
	'amber'        => '#f59e0b',
	'crimson'      => '#dc2626',
	'charcoal'     => '#374151',
	'off white'    => '#f9fafb',
	'off-white'    => '#f9fafb',
	'dark blue'    => '#1e40af',
	'dark-blue'    => '#1e40af',
	'light blue'   => '#93c5fd',
	'light-blue'   => '#93c5fd',
	'dark green'   => '#166534',
	'dark-green'   => '#166534',
	'light green'  => '#86efac',
	'light-green'  => '#86efac',
	'dark red'     => '#991b1b',
	'dark-red'     => '#991b1b',
	'royal blue'   => '#2563eb',
	'royal-blue'   => '#2563eb',
	'hot pink'     => '#f472b6',
	'hot-pink'     => '#f472b6',
	'baby blue'    => '#bfdbfe',
	'baby-blue'    => '#bfdbfe',
	'neon green'   => '#4ade80',
	'neon-green'   => '#4ade80',
	'mustard'      => '#ca8a04',
	'burgundy'     => '#881337',
	'mauve'        => '#c084fc',
	'rust'         => '#b45309',
	'slate'        => '#64748b',
	'nude'         => '#e8c9a0',
	'camel'        => '#c19a6b',
];
?>
<div class="gcow-attribute-selectors"
	data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
	data-variations="<?php echo esc_attr( $bump_data['variations_json'] ); ?>"
	style="--gcow-btn-color:<?php echo esc_attr( $gcow_btn_color ); ?>;padding:0 <?php echo esc_attr( $gcow_padding_x ); ?>px 8px;">

	<?php foreach ( $bump_data['attribute_selectors'] as $gcow_attr_key => $gcow_attr_data ) :
		$gcow_taxonomy = str_replace( 'attribute_', '', $gcow_attr_key );
	?>

	<?php if ( $use_swatches ) : ?>

		<div style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">
			<?php echo esc_html( $gcow_attr_data['label'] ); ?>:
			<span class="gcow-swatch-label"
				data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
				data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
				style="color:#1f2937;font-weight:600;"></span>
		</div>

		<div class="gcow-swatch-group" style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px;">
			<?php foreach ( $gcow_attr_data['options'] as $gcow_option ) :

				// 1. Try swatch-plugin term meta
				$gcow_term      = taxonomy_exists( $gcow_taxonomy ) ? get_term_by( 'slug', $gcow_option['value'], $gcow_taxonomy ) : null;
				$gcow_color_hex = $gcow_term ? get_term_meta( $gcow_term->term_id, 'product_attribute_color', true ) : '';

				// 2. Fall back to color-name map (check slug then label)
				if ( ! $gcow_color_hex || ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $gcow_color_hex ) ) {
					$gcow_slug_key  = strtolower( str_replace( [ '_' ], ' ', $gcow_option['value'] ) );
					$gcow_label_key = strtolower( $gcow_option['label'] );
					$gcow_color_hex = $gcow_color_map[ $gcow_slug_key ] ?? $gcow_color_map[ $gcow_label_key ] ?? '';
				}

				$gcow_has_color = $gcow_color_hex && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $gcow_color_hex );
			?>

			<?php if ( $gcow_has_color ) : ?>
				<button type="button"
					class="gcow-swatch gcow-swatch-color"
					data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
					data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
					data-value="<?php echo esc_attr( $gcow_option['value'] ); ?>"
					data-label="<?php echo esc_attr( $gcow_option['label'] ); ?>"
					title="<?php echo esc_attr( $gcow_option['label'] ); ?>"
					style="width:26px;height:26px;border-radius:50%;background-color:<?php echo esc_attr( $gcow_color_hex ); ?>;border:2px solid #e5e7eb;cursor:pointer;padding:0;flex-shrink:0;"></button>
			<?php else : ?>
				<button type="button"
					class="gcow-swatch gcow-swatch-text"
					data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
					data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
					data-value="<?php echo esc_attr( $gcow_option['value'] ); ?>"
					data-label="<?php echo esc_attr( $gcow_option['label'] ); ?>"
					style="padding:3px 10px;border-radius:4px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap;flex-shrink:0;line-height:1.5;"><?php echo esc_html( $gcow_option['label'] ); ?></button>
			<?php endif; ?>

			<?php endforeach; ?>
		</div>

		<select class="gcow-attr-select"
			data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
			data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
			style="display:none;">
			<option value="">— <?php echo esc_html( $gcow_attr_data['label'] ); ?> —</option>
			<?php foreach ( $gcow_attr_data['options'] as $gcow_option ) : ?>
			<option value="<?php echo esc_attr( $gcow_option['value'] ); ?>"><?php echo esc_html( $gcow_option['label'] ); ?></option>
			<?php endforeach; ?>
		</select>

	<?php else : ?>

		<select class="gcow-attr-select"
			data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
			data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
			style="width:100%;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#374151;background-color:#fff;margin-bottom:6px;cursor:pointer;appearance:none;-webkit-appearance:none;background-image:url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22%3e%3cpath d=%22M6 9l6 6 6-6%22/%3e%3c/svg%3e');background-repeat:no-repeat;background-position:right 8px center;background-size:16px;padding-right:30px;">
			<option value="">— <?php echo esc_html( $gcow_attr_data['label'] ); ?> —</option>
			<?php foreach ( $gcow_attr_data['options'] as $gcow_option ) : ?>
			<option value="<?php echo esc_attr( $gcow_option['value'] ); ?>"><?php echo esc_html( $gcow_option['label'] ); ?></option>
			<?php endforeach; ?>
		</select>

	<?php endif; ?>

	<?php endforeach; ?>
</div>
