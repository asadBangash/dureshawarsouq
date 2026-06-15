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

	$(document).on('click', '.product-unit-selector .unit-option', function(event) {
		event.preventDefault();
		var $option = $(this);
		$('.product-unit-selector .unit-option').removeClass('active');
		$option.addClass('active');
		var unit = $option.data('unit');
		var price = parseFloat($option.data('price'));
		$('#selected_unit').val(unit);
		updateProductPrice(price);
	});
	
});

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



