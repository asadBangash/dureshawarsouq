var $ = jQuery.noConflict();

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	onViewCart();
	onWishlist();
	
	$(document).on("click", ".product_addtocart", function(event) {
		event.preventDefault();

		var id = $(this).data('id');
		var qty = $("#quantity").val();
		var unit = ($("#selected_unit").length && $("#selected_unit").val()) ? $("#selected_unit").val() : 'piece';
		var shade = ($("#selected_shade").length && $("#selected_shade").val()) ? $("#selected_shade").val() : '';

		if((qty == undefined) || (qty == '') || (qty <= 0)){
			onErrorMsg(TEXT['Please enter quantity.']);
			return;
		}
		if(is_stock == 1){
			var stockqty = $(this).data('stockqty');
			if(is_stock_status == 1){
				if(qty > stockqty){
					onErrorMsg(TEXT['The value must be less than or equal to']);
					return;
				}
			}else{
				onErrorMsg(TEXT['This product out of stock.']);
				return;
			}
		}
		
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty+'?unit='+unit+'&shade='+encodeURIComponent(shade),
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });
	
	$(document).on("click", ".product_buy_now", function(event) {
		event.preventDefault();

		var id = $(this).data('id');
		var qty = $("#quantity").val();
		var unit = ($("#selected_unit").length && $("#selected_unit").val()) ? $("#selected_unit").val() : 'piece';
		var shade = ($("#selected_shade").length && $("#selected_shade").val()) ? $("#selected_shade").val() : '';
		
		if((qty == undefined) || (qty == '') || (qty <= 0)){
			onErrorMsg(TEXT['Please enter quantity.']);
			return;
		}
		if(is_stock == 1){
			var stockqty = $(this).data('stockqty');
			if(is_stock_status == 1){
				if(qty > stockqty){
					onErrorMsg(TEXT['The value must be less than or equal to']);
					return;
				}
			}else{
				onErrorMsg(TEXT['This product out of stock.']);
				return;
			}
		}
		
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty+'?unit='+unit+'&shade='+encodeURIComponent(shade),
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					// onSuccessMsg(msg);
					window.location.href = base_url + '/checkout';
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });
	
	$(document).on("click", ".addtocart", function(event) {
		event.preventDefault();
		
		var id = $(this).data('id');
		var qty = 0;
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });	
	
	$(document).on("click", ".addtowishlist", function(event) {
		event.preventDefault();
		
		var id = $(this).data('id');

		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_wishlist/'+id,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onWishlist();
			}
		});
    });

	$(document).on('click', '.cart-qty-minus, .cart-qty-plus', function(event) {
		event.preventDefault();

		var $btn = $(this);
		var rowid = $btn.data('rowid');
		var qty = parseInt($btn.data('qty'), 10) || 1;
		var stockqty = parseInt($btn.data('stockqty'), 10) || 999;
		var isPlus = $btn.hasClass('cart-qty-plus');
		var newQty = isPlus ? qty + 1 : qty - 1;

		if (isPlus && newQty > stockqty) {
			var limitMsg = (typeof TEXT !== 'undefined' && TEXT['The value must be less than or equal to'])
				? TEXT['The value must be less than or equal to'] + ' ' + stockqty
				: 'The value must be less than or equal to ' + stockqty;
			onErrorMsg(limitMsg);
			return;
		}

		onUpdateCartQty(rowid, newQty);
	});
});

function onViewCart() {

    $.ajax({
		type : 'GET',
		url: base_url + '/frontend/view_cart',
		dataType:"json",
		success: function (data) {
			if(data.items == ''){
				$(".has_item_empty").show();
				$(".has_cart_item").hide();
				$(".total_qty").text(0);
			}else{
				$(".has_item_empty").hide();
				$(".has_cart_item").show();
				
				$('#tp_cart_data').html(data.items);
				$('#tp_cart_data_for_mobile').html(data.items);
				
				$(".total_qty").text(data.total_qty);
				$(".sub_total").text(data.sub_total);
				$(".tax").text(data.tax);
				$(".tp_total").text(data.total);
			}
		}
	});
}

function onRemoveToCart(rowid) {
	$.ajax({
		type : 'GET',
		url: base_url + '/frontend/remove_to_cart/'+rowid,
		dataType:"json",
		success: function (response) {
			
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				onSuccessMsg(msg);
			} else {
				onErrorMsg(msg);
			}
			
			onViewCart();
		}
	});
}

function onUpdateCartQty(rowid, qty) {
	$.ajax({
		type: 'GET',
		url: base_url + '/frontend/update_cart/' + rowid + '/' + qty,
		dataType: 'json',
		success: function (response) {
			if (response.msgType !== 'success') {
				onErrorMsg(response.msg);
				return;
			}

			var onCheckout = window.location.pathname.indexOf('/checkout') !== -1;

			if (response.removed) {
				$('#row_delete_' + rowid).remove();
				$('#checkout_row_' + rowid).remove();

				if (onCheckout && $('[id^="checkout_row_"]').length === 0) {
					window.location.href = base_url + '/cart';
					return;
				}
				if (!onCheckout && $('.shopping-cart tbody tr').length === 0) {
					window.location.href = base_url + '/cart';
					return;
				}
			} else {
				var $row = $('#row_delete_' + rowid);
				if ($row.length) {
					$row.find('.cart-qty-input').val(response.qty);
					$row.find('.cart-qty-minus, .cart-qty-plus').data('qty', response.qty);
					if (response.line_total) {
						$row.find('.pro-total-price').text(response.line_total);
					}
				}

				var $checkoutRow = $('#checkout_row_' + rowid);
				if ($checkoutRow.length) {
					$checkoutRow.find('.cart-qty-display, .checkout-qty-count').text(response.qty);
					$checkoutRow.find('.cart-qty-minus, .cart-qty-plus').data('qty', response.qty);
					if (response.line_total) {
						$checkoutRow.find('.checkout-line-total').text(response.line_total);
					}
					if (response.line_sub_price) {
						$checkoutRow.find('.sub-price').html(response.line_sub_price);
					}
				}
			}

			onViewCart();

			if (typeof onViewCartData === 'function') {
				onViewCartData();
			} else if (response.sub_total) {
				$('.viewcart_price_total').text(response.price_total);
				$('.viewcart_tax').text(response.tax);
				$('.viewcart_sub_total').text(response.sub_total);
				$('.viewcart_total').text(response.total);
			}

			if (onCheckout && typeof refreshCheckoutOrderSummary === 'function') {
				refreshCheckoutOrderSummary(response);
			}
		}
	});
}

function onWishlist() {

    $.ajax({
		type : 'GET',
		url: base_url + '/frontend/count_wishlist',
		dataType:"json",
		success: function (data) {
			$(".count_wishlist").text(data);
		}
	});
}
