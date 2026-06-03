/**
 * Modern home hero slider (Owl Carousel)
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $heroModern = $('.hero-slider-modern__track');
		if (!$heroModern.length) {
			return;
		}

		var heroAutoplayMs = 5500;

		$heroModern.owlCarousel({
			navText: [
				'<i class="bi bi-chevron-left"></i>',
				'<i class="bi bi-chevron-right"></i>'
			],
			rtl: typeof isRTL !== 'undefined' ? isRTL : false,
			loop: true,
			nav: true,
			dots: true,
			autoplay: true,
			autoplayTimeout: heroAutoplayMs,
			autoplayHoverPause: true,
			mouseDrag: true,
			smartSpeed: 900,
			items: 1,
			responsive: { 0: { items: 1 } }
		});

		var $progressBar = $('.hero-slider-modern__progress-bar');

		function resetHeroProgress() {
			$progressBar.css({ width: '0%', transition: 'none' });
			setTimeout(function () {
				$progressBar.css({
					width: '100%',
					transition: 'width ' + heroAutoplayMs + 'ms linear'
				});
			}, 50);
		}

		resetHeroProgress();
		$heroModern.on('changed.owl.carousel', resetHeroProgress);
	});
})(jQuery);
