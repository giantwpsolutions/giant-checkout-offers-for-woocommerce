/**
 * Giant Checkout Offers for WooCommerce - Frontend JavaScript
 */

(function ($) {
	'use strict';

	$(document).ready(function () {

		/**
		 * Find the variation whose attributes match the user's selections.
		 * Variations with '' (Any) for an attribute match any customer-provided value.
		 */
		function findMatchingVariation(variations, selectedAttrs) {
			if (!Object.keys(selectedAttrs).length) return null;

			return variations.find(function (v) {
				var attrs = v.attributes || {};
				return Object.keys(selectedAttrs).every(function (key) {
					var varVal = attrs[key] !== undefined ? attrs[key] : '';
					var selVal = selectedAttrs[key];
					return varVal === '' || varVal === selVal;
				});
			}) || null;
		}

		/**
		 * Read all attribute selects for a bump and attempt to find a matching variation.
		 */
		function syncVariationForBump(bumpId) {
			var $container = $('.gcow-attribute-selectors[data-bump-id="' + bumpId + '"]');
			if (!$container.length) return;

			var variations = [];
			try {
				variations = JSON.parse($container.attr('data-variations') || '[]');
			} catch (e) {}

			var selectedAttrs = {};
			$container.find('.gcow-attr-select').each(function () {
				var key = $(this).data('attr-key');
				var val = $(this).val();
				if (val) {
					selectedAttrs[key] = val;
				}
			});

			var match = findMatchingVariation(variations, selectedAttrs);
			var varId = match ? match.id : 0;

			var $checkbox = $('.gcow-bump-checkbox[data-bump-id="' + bumpId + '"]');
			$checkbox.attr('data-variation-id', varId);
			$checkbox.data('selected-attrs', JSON.stringify(selectedAttrs));
		}

		// Handle attribute dropdown changes
		$(document).on('change', '.gcow-attr-select', function () {
			syncVariationForBump($(this).data('bump-id'));
		});

		// Handle bump checkbox change
		$(document).on('change', '.gcow-bump-checkbox', function () {
			var $checkbox   = $(this);
			var isChecked   = $checkbox.is(':checked');
			var productId   = $checkbox.data('product-id');
			var variationId = parseInt($checkbox.attr('data-variation-id'), 10) || 0;
			var bumpId      = $checkbox.data('bump-id');
			var selAttrs    = $checkbox.data('selected-attrs') || $checkbox.attr('data-variation-attrs') || '{}';

			if (!isChecked) {
				$checkbox.prop('checked', true);
				return;
			}

			// Validate all attribute selects have a value
			var $container  = $('.gcow-attribute-selectors[data-bump-id="' + bumpId + '"]');
			var allSelected = true;
			$container.find('.gcow-attr-select').each(function () {
				if (!$(this).val()) {
					var label = $(this).find('option:first').text().replace(/^[-\s]+/, '').replace(/[-\s]+$/, '');
					showNotice('error', label + ' is required.');
					allSelected = false;
					return false;
				}
			});

			if (!allSelected) {
				$checkbox.prop('checked', false);
				return;
			}

			addBumpToCart(productId, variationId, bumpId, selAttrs, $checkbox);
		});

		/**
		 * Add bump product to cart via AJAX — no page reload.
		 */
		function addBumpToCart(productId, variationId, bumpId, selAttrs, $checkbox) {
			$checkbox.prop('disabled', true);

			$.ajax({
				url:  gcowFrontend.ajaxUrl,
				type: 'POST',
				data: {
					action:          'gcow_add_bump_to_cart',
					nonce:           gcowFrontend.nonce,
					product_id:      productId,
					variation_id:    variationId,
					variation_attrs: selAttrs,
					bump_id:         bumpId
				},
				success: function (response) {
					if (response.success) {
						showNotice('success', response.data.message || 'Product added to your order!');
						// Cart page uses wc_update_cart, checkout uses update_checkout
						if ($('form.woocommerce-cart-form').length) {
							$(document.body).trigger('wc_update_cart');
						} else {
							$(document.body).trigger('update_checkout');
						}
						$checkbox.prop('disabled', false);
					} else {
						showNotice('error', response.data.message || 'Failed to add product.');
						$checkbox.prop('checked', false).prop('disabled', false);
					}
				},
				error: function () {
					showNotice('error', 'Failed to add product to cart. Please try again.');
					$checkbox.prop('checked', false).prop('disabled', false);
				}
			});
		}

		/**
		 * Show an inline WooCommerce-style notice without page reload.
		 */
		function showNotice(type, message) {
			var cssClass = type === 'success' ? 'woocommerce-message' : 'woocommerce-error';
			var $notice  = $('<div role="alert" class="' + cssClass + '"></div>').text(message);

			var $target = $('.woocommerce-notices-wrapper').first();
			if ($target.length) {
				$target.empty().append($notice);
			} else {
				$('form.checkout, .woocommerce-checkout, form.woocommerce-cart-form').first().prepend($notice);
			}

			if ($notice.offset()) {
				$('html, body').animate({ scrollTop: $notice.offset().top - 80 }, 300);
			}

			if (type === 'success') {
				setTimeout(function () {
					$notice.fadeOut(400, function () { $(this).remove(); });
				}, 4000);
			}
		}
	});

})(jQuery);
