var $ = jQuery.noConflict();

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$(document).on('click', '.pagination a', function(event){
		event.preventDefault(); 
		var page = $(this).attr('href').split('page=')[1];
		onPaginationDataLoad(page);
	});

	// Initialise shade label with the pre-selected (first) shade
	var $activeShade = $('.product-shades .shade-option.active').first();
	if ($activeShade.length) {
		$('#selected_shade_label').text($activeShade.data('name'));
	}

	$(document).on('click', '.product-shades .shade-option', function(event) {
		event.preventDefault();
		var $option = $(this);
		$('.product-shades .shade-option').removeClass('active');
		$option.addClass('active');

		var name = $option.data('name');
		$('#selected_shade').val(name);
		$('#selected_shade_label').text(name);

		var price = parseFloat($option.data('price'));
		if (!isNaN(price)) {
			updateProductPrice(price);
		} else if (typeof base_product_price !== 'undefined') {
			updateProductPrice(parseFloat(base_product_price));
		}
	});

	// Frequently Bought Together
	function fbtUpdateTotal() {
		var total = 0;
		$('.fbt_check:checked').each(function () {
			var p = parseFloat($(this).data('price'));
			if (!isNaN(p)) { total += p; }
		});
		$('#fbt_total_price').text(fbtFormatPrice(total));
	}

	if ($('.fbt_section').length) {
		fbtUpdateTotal();
	}

	$(document).on('change', '.fbt_check', function () {
		fbtUpdateTotal();
	});

	$(document).on('click', '#fbt_add_all', function (event) {
		event.preventDefault();

		var selectedShade = ($('#selected_shade').length && $('#selected_shade').val()) ? $('#selected_shade').val() : '';

		var items = [];
		$('.fbt_check:checked').each(function () {
			var id = $(this).data('id');
			// Carry the chosen colour shade for the current product only
			var shade = (typeof item_id !== 'undefined' && String(id) === String(item_id)) ? selectedShade : '';
			items.push({ id: id, shade: shade });
		});
		if (items.length === 0) { return; }

		var $btn = $(this);
		$btn.addClass('disabled').text('...');

		fbtAddSequential(items, 0, function () {
			if (typeof onViewCart === 'function') { onViewCart(); }
			window.location.href = base_url + '/cart';
		});
	});

});

function fbtFormatPrice(price) {
	var formatted = price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	if (typeof currency_position !== 'undefined' && currency_position === 'left') {
		return currency_icon + formatted;
	}
	return formatted + currency_icon;
}

function fbtAddSequential(items, index, done) {
	if (index >= items.length) {
		done();
		return;
	}
	var item = items[index];
	var url = base_url + '/frontend/add_to_cart/' + item.id + '/1?unit=piece';
	if (item.shade) {
		url += '&shade=' + encodeURIComponent(item.shade);
	}
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'json',
		complete: function () {
			fbtAddSequential(items, index + 1, done);
		}
	});
}

function updateProductPrice(price) {
	if (isNaN(price) || price === null || price === '') {
		return;
	}
	var formatted = price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	var display = '';
	if (typeof currency_position !== 'undefined' && currency_position === 'left') {
		display = currency_icon + formatted;
	} else {
		display = formatted + currency_icon;
	}
	$('#product-display-price').text(display);
}

function onPaginationDataLoad(page) {

	$.ajax({
		url:base_url + "/frontend/getProductReviewsGrid",
		data:{page:page,item_id:item_id},
		success:function(data){
			$('#tp_datalist').html(data);
		}
	});
}



