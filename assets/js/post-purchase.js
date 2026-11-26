/**
 * Giant Checkout Offers — Post-Purchase Upsell (Thank You Page)
 *
 * Transforms the shared checkout bump template into an Accept / Decline widget.
 * The checkout-style checkbox CTA is hidden; two post-purchase buttons are injected.
 */

(function ($) {
	'use strict';

	$(document).ready(function () {

		// ── Setup ────────────────────────────────────────────────────────────────

		$('.gcow-pp-wrap').each(function () {
			var $wrap = $(this);

			// Hide the checkout-style CTA (checkbox inside a label, or standalone).
			$wrap.find('.gcow-bump-checkbox').each(function () {
				var $label = $(this).closest('label');
				if ($label.length) {
					$label.hide();
				} else {
					$(this).css('display', 'none');
				}
			});

			// Inject Accept / Decline buttons at the bottom of the card.
			var $card = $wrap.find('.gcow-bump-container > div').first();
			$card.append(
				'<div class="gcow-pp-cta" style="padding:12px 16px;border-top:1px solid #f3f4f6;">' +
					'<button type="button" class="gcow-pp-accept" ' +
						'style="width:100%;padding:12px 20px;background:linear-gradient(135deg,#4f46e5,#7c3aed);' +
						'color:#fff;font-size:14px;font-weight:700;border:none;border-radius:8px;' +
						'cursor:pointer;letter-spacing:.2px;margin-bottom:8px;display:block;">' +
						gcowPostPurchase.i18n.accept +
					'</button>' +
					'<button type="button" class="gcow-pp-decline" ' +
						'style="width:100%;padding:8px;background:transparent;color:#9ca3af;' +
						'font-size:12px;border:none;cursor:pointer;text-decoration:underline;display:block;">' +
						gcowPostPurchase.i18n.decline +
					'</button>' +
				'</div>'
			);
		});

		// ── Accept ───────────────────────────────────────────────────────────────

		$(document).on('click', '.gcow-pp-accept', function () {
			var $btn      = $(this);
			var $wrap     = $btn.closest('.gcow-pp-wrap');
			var orderId   = $wrap.data('order-id');
			var bumpId    = $wrap.data('bump-id');
			var productId = $wrap.data('product-id');

			if (!$btn.data('original-text')) {
				$btn.data('original-text', $btn.text().trim());
			}

			$btn.prop('disabled', true).text(gcowPostPurchase.i18n.processing);

			$.ajax({
				url:  gcowPostPurchase.ajaxUrl,
				type: 'POST',
				data: {
					action:     'gcow_post_purchase_accept',
					nonce:      gcowPostPurchase.nonce,
					bump_id:    bumpId,
					order_id:   orderId,
					product_id: productId,
				},
				success: function (response) {
					if (response.success && response.data.pay_url) {
						window.location.href = response.data.pay_url;
					} else {
						showError($wrap, (response.data && response.data.message) || gcowPostPurchase.i18n.error);
						$btn.prop('disabled', false).text($btn.data('original-text'));
					}
				},
				error: function () {
					showError($wrap, gcowPostPurchase.i18n.error);
					$btn.prop('disabled', false).text($btn.data('original-text'));
				},
			});
		});

		// ── Decline ──────────────────────────────────────────────────────────────

		$(document).on('click', '.gcow-pp-decline', function () {
			$(this).closest('.gcow-pp-wrap').slideUp(300, function () {
				$(this).remove();
			});
		});

		// ── Helpers ──────────────────────────────────────────────────────────────

		function showError($wrap, message) {
			$wrap.find('.gcow-pp-error').remove();
			$('<p class="gcow-pp-error"></p>')
				.text(message)
				.css({ color: '#dc2626', fontSize: '13px', margin: '0 0 8px', textAlign: 'center' })
				.insertBefore($wrap.find('.gcow-pp-accept'));
		}

	});

})(jQuery);
