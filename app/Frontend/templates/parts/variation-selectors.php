<?php
defined( 'ABSPATH' ) || exit;
/**
 * Variation / Attribute Selectors partial.
 * Included by all bump templates when attribute_selectors is non-empty.
 *
 * Required variables (inherited from parent template scope):
 *   @var array $bump_data
 *   @var array $styles
 */

if ( empty( $bump_data['attribute_selectors'] ) ) {
	return;
}
?>
<div class="gcow-attribute-selectors"
	data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
	data-variations="<?php echo esc_attr( $bump_data['variations_json'] ); ?>"
	style="padding: 0 <?php echo esc_attr( absint( $styles['padding'] ?? 12 ) ); ?>px 8px;">
	<?php foreach ( $bump_data['attribute_selectors'] as $gcow_attr_key => $gcow_attr_data ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
	<select class="gcow-attr-select"
		data-bump-id="<?php echo esc_attr( $bump_data['id'] ); ?>"
		data-attr-key="<?php echo esc_attr( $gcow_attr_key ); ?>"
		style="width: 100%; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; color: #374151; background-color: #ffffff; margin-bottom: 6px; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b7280%22 stroke-width=%222%22%3e%3cpath d=%22M6 9l6 6 6-6%22/%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 8px center; background-size: 16px; padding-right: 30px;">
		<option value="">— <?php echo esc_html( $gcow_attr_data['label'] ); ?> —</option>
		<?php foreach ( $gcow_attr_data['options'] as $gcow_option ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
		<option value="<?php echo esc_attr( $gcow_option['value'] ); ?>"><?php echo esc_html( $gcow_option['label'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php endforeach; ?>
</div>
