/**
 * Giant Checkout Offers — Auto Product Suggestion Slider
 * Mobile: 1 product per view  |  Desktop (≥768px): 2.5 products per view
 */
(function () {
	'use strict';

	function initSlider(wrap) {
		var viewport = wrap.querySelector('.gcow-ps-viewport');
		var track    = wrap.querySelector('.gcow-ps-track');
		var items    = Array.from(track.querySelectorAll('.gcow-ps-item'));
		var prev     = wrap.querySelector('.gcow-ps-prev');
		var next     = wrap.querySelector('.gcow-ps-next');
		var count    = items.length;
		var current  = 0;
		var touchStartX = 0;

		function perView() {
			return window.innerWidth >= 768 ? 2.5 : 1;
		}

		function itemWidth() {
			return viewport.clientWidth / perView();
		}

		function maxSlide() {
			return Math.max(0, count - Math.floor(perView()));
		}

		function setItemWidths() {
			var w = itemWidth();
			items.forEach(function (item) {
				item.style.width    = w + 'px';
				item.style.minWidth = w + 'px';
				item.style.maxWidth = w + 'px';
			});
		}

		function goTo(n) {
			current = Math.max(0, Math.min(n, maxSlide()));
			track.style.transform = 'translateX(' + (-current * itemWidth()) + 'px)';
			if (prev) prev.disabled = current === 0;
			if (next) next.disabled = current >= maxSlide();
		}

		if (prev) prev.addEventListener('click', function () { goTo(current - 1); });
		if (next) next.addEventListener('click', function () { goTo(current + 1); });

		// Touch / swipe
		track.addEventListener('touchstart', function (e) {
			touchStartX = e.touches[0].clientX;
		}, { passive: true });

		track.addEventListener('touchend', function (e) {
			var diff = touchStartX - e.changedTouches[0].clientX;
			if (Math.abs(diff) > 40) {
				goTo(diff > 0 ? current + 1 : current - 1);
			}
		}, { passive: true });

		// Resize — debounced
		var resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function () {
				setItemWidths();
				goTo(current);
			}, 100);
		});

		setItemWidths();
		goTo(0);
	}

	function init() {
		document.querySelectorAll('.gcow-ps-wrap').forEach(initSlider);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
