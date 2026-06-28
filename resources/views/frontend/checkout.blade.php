@extends('layouts.frontend')

@section('title', __('Checkout'))
@php 
$gtext = gtext(); 
$gtax = getTax();
$tax_rate = $gtax['percentage'];
config(['cart.tax' => $tax_rate]);
@endphp

@section('meta-content')
	<meta name="keywords" content="{{ $gtext['og_keywords'] }}" />
	<meta name="description" content="{{ $gtext['og_description'] }}" />
	<meta property="og:title" content="{{ $gtext['og_title'] }}" />
	<meta property="og:site_name" content="{{ $gtext['site_name'] }}" />
	<meta property="og:description" content="{{ $gtext['og_description'] }}" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="{{ url()->current() }}" />
	<meta property="og:image" content="{{ asset('public/media/'.$gtext['og_image']) }}" />
	<meta property="og:image:width" content="600" />
	<meta property="og:image:height" content="315" />
	@if($gtext['fb_publish'] == 1)
	<meta name="fb:app_id" property="fb:app_id" content="{{ $gtext['fb_app_id'] }}" />
	@endif
	<meta name="twitter:card" content="summary_large_image">
	@if($gtext['twitter_publish'] == 1)
	<meta name="twitter:site" content="{{ $gtext['twitter_id'] }}">
	<meta name="twitter:creator" content="{{ $gtext['twitter_id'] }}">
	@endif
	<meta name="twitter:url" content="{{ url()->current() }}">
	<meta name="twitter:title" content="{{ $gtext['og_title'] }}">
	<meta name="twitter:description" content="{{ $gtext['og_description'] }}">
	<meta name="twitter:image" content="{{ asset('public/media/'.$gtext['og_image']) }}">
@endsection

@section('header')
@include('frontend.partials.header')
@endsection

@section('content')

<main class="main">
	<!-- Page Breadcrumb -->
	<div class="breadcrumb-section">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('Checkout') }}</li>
						</ol>
					</nav>
				</div>
				<div class="col-lg-6">
					<div class="page-title">
						<h1>{{ __('Checkout') }}</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Page Breadcrumb/ -->
	
	<!-- Inner Section -->
	<section class="inner-section inner-section-bg my_card">
		<div class="container">
			<form novalidate="" data-validate="parsley" id="checkout_formid">
				@csrf
				<div class="row">
					<div class="col-lg-7">
						<h5>{{ __('Shipping Information') }}</h5>
						<p>{{ __('Already have an account?') }} <strong><a href="{{ route('frontend.login') }}">{{ __('login') }}</a></strong></p>
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<input id="name" name="name" type="text" placeholder="{{ __('Name') }}" value="@if(isset(Auth::user()->name)) {{ Auth::user()->name }} @endif" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text name_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<input id="email" name="email" type="email" placeholder="{{ __('Email Address') }}" value="@if(isset(Auth::user()->email)) {{ Auth::user()->email }} @endif" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text email_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="phone" name="phone" type="text" placeholder="{{ __('Phone') }}" value="@if(isset(Auth::user()->phone)) {{ Auth::user()->phone }} @endif" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text phone_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<select id="country" name="country" class="form-control parsley-validated" data-required="true">
									<option value="">{{ __('Country') }}</option>
									@foreach($country_list as $row)
									<option value="{{ $row->country_name }}">
										{{ $row->country_name }}
									</option>
									@endforeach
									</select>
									<span class="text-danger error-text country_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="state" name="state" type="text" placeholder="{{ __('State') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text state_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<input id="zip_code" name="zip_code" type="text" placeholder="{{ __('Zip Code') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text zip_code_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="city" name="city" type="text" placeholder="{{ __('City') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text city_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<textarea id="address" name="address" placeholder="{{ __('Address') }}" rows="2" class="form-control parsley-validated" data-required="true">@if(isset(Auth::user()->address)) {{ Auth::user()->address }} @endif</textarea>
									<span class="text-danger error-text address_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="checkboxlist">
									<label class="checkbox-title">
										<input id="new_account" name="new_account" type="checkbox">{{ __('Register an account with above information?') }} 
									</label>
								</div>
								@if ($errors->has('password'))
								<span class="text-danger">{{ $errors->first('password') }}</span>
								@endif
							</div>
						</div>
						
						<div class="row hideclass" id="new_account_pass">
							<div class="col-md-6">
								<div class="mb-3">
									<input type="password" name="password" id="password" class="form-control" placeholder="{{ __('Password') }}">
									<span class="text-danger error-text password_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('Confirm password') }}">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="mb-3 mt10">
									<textarea name="comments" class="form-control" placeholder="Note" rows="2"></textarea>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-5">
						<div class="carttotals-card">
							<div class="carttotals-head">{{ __('Order Summary') }}</div>
							<div class="carttotals-body">
								<table class="table">
									<tbody>
										@php 
										$CartDataList = Cart::instance('shopping')->content();
										$CartDataArr = array();
										@endphp
										
										@foreach($CartDataList as $row)
											@php
											$row->setTaxRate($tax_rate);
											Cart::instance('shopping')->update($row->rowId, $row->qty);

											$checkoutProductId = $row->options->product_id ?? $row->id;
											$checkoutProduct = \App\Models\Product::find($checkoutProductId);
											$checkoutStockQty = ($checkoutProduct && $checkoutProduct->is_stock == 1 && $checkoutProduct->stock_status_id == 1)
												? (int) $checkoutProduct->stock_qty
												: 999;
											
											$data = array(
												'rowId' => $row->rowId, 
												'id' => $row->id,
												'product_id' => $checkoutProductId,
												'qty' => $row->qty,
												'stock_qty' => $checkoutStockQty,
												'name' => $row->name, 
												'price' => $row->price, 
												'weight' => $row->weight, 
												'thumbnail' => $row->options->thumbnail, 
												'unit' => $row->options->unit,
												'seller_id' => $row->options->seller_id,
												'seller_name' => $row->options->seller_name,
												'store_name' => $row->options->store_name,
												'store_logo' => $row->options->store_logo,
												'store_url' => $row->options->store_url,
												'seller_email' => $row->options->seller_email,
												'seller_phone' => $row->options->seller_phone,
												'seller_address' => $row->options->seller_address
											);
											
											$CartDataArr[$row->options->seller_id][] = $data;
											@endphp
										@endforeach
										
										@php $CartData_Arr = array(); @endphp
										@foreach($CartDataArr as $aRows)
											@foreach($aRows as $row)
												@php $CartData_Arr[] = $row; @endphp
											@endforeach
										@endforeach
										
										@php 
										$tempSellerId = ''; 
										$SellerCount = 0; 
										@endphp
		
										@foreach($CartData_Arr as $row)
											@php
											if($row['unit'] == '0'){
												$unit = '';
											}else{
												$unit = '<div class="checkout-qty-wrap cart-qty-wrap">'
													.'<button type="button" class="qty-btn cart-qty-minus" data-rowid="'.$row['rowId'].'" data-qty="'.$row['qty'].'" data-stockqty="'.$row['stock_qty'].'">-</button>'
													.'<span class="cart-qty-display">'.$row['qty'].'</span>'
													.'<span class="checkout-qty-unit">'.$row['unit'].'</span>'
													.'<button type="button" class="qty-btn cart-qty-plus" data-rowid="'.$row['rowId'].'" data-qty="'.$row['qty'].'" data-stockqty="'.$row['stock_qty'].'">+</button>'
													.'</div>';
											}
											@endphp
											
											@if($tempSellerId != $row['seller_id'])
											@php 
											$tempSellerId=$row['seller_id']; 
											$SellerCount++;
											@endphp
											@endif
											
											@if($gtext['currency_position'] == 'left')
											<tr id="checkout_row_{{ $row['rowId'] }}">
												<td>
													<p class="title"><a href="{{ route('frontend.product', [$row['product_id'], str_slug($row['name'])]) }}">{{ $row['name'] }}</a></p>
													<p class="sub-title">@php echo $unit; @endphp</p>
												</td>
												<td>
													<p class="price checkout-line-total">{{ $gtext['currency_icon'] }}{{ number_format($row['price']*$row['qty']) }}</p>
													<p class="sub-price">{{ $gtext['currency_icon'] }}{{ $row['price'] }} x <span class="checkout-qty-count">{{ $row['qty'] }}</span></p>
												</td>
											</tr>
											@else
											<tr id="checkout_row_{{ $row['rowId'] }}">
												<td>
													<p class="title"><a href="{{ route('frontend.product', [$row['product_id'], str_slug($row['name'])]) }}">{{ $row['name'] }}</a></p>
													<p class="sub-title">@php echo $unit; @endphp</p>
												</td>
												<td>
													<p class="price checkout-line-total">{{ number_format($row['price']*$row['qty']) }}{{ $gtext['currency_icon'] }}</p>
													<p class="sub-price">{{ $row['price'] }}{{ $gtext['currency_icon'] }} x <span class="checkout-qty-count">{{ $row['qty'] }}</span></p>
												</td>
											</tr>
											@endif
										@endforeach
										
										@php
											if($gtext['currency_position'] == 'left'){
												$ShippingFee = $gtext['currency_icon'].'<span class="shipping_fee">0</span>'; 
												$tax = $gtext['currency_icon'].Cart::instance('shopping')->tax();
												$total = $gtext['currency_icon'].'<span class="total_amount">'.Cart::instance('shopping')->total().'</span>';
											}else{
												$ShippingFee = '<span class="shipping_fee">0</span>'.$gtext['currency_icon'];
												$tax = Cart::instance('shopping')->tax().$gtext['currency_icon'];
												$total = '<span class="total_amount">'.Cart::instance('shopping')->total().'</span>'.$gtext['currency_icon'];
											}
										@endphp
										
										<tr><td colspan="2"><span class="title">{{ __('Shipping Fee') }} </span><span class="price">@php echo $ShippingFee; @endphp</span></td></tr>
										<tr><td colspan="2"><span class="title">{{ __('Tax') }}</span><span class="price checkout_tax">{{ $tax }}</span></td></tr>
										<tr><td colspan="2"><span class="total">{{ __('Total') }}</span><span class="total-price">@php echo $total; @endphp</span></td></tr>
									</tbody>
								</table>
								@if(count($shipping_list)>0)
								<h5>{{ __('Shipping Method') }}</h5>
								<div class="row">
									<div class="col-md-12">
										<span class="text-danger error-text shipping_method_error"></span>
										@foreach($shipping_list as $row)
											@php
												if($gtext['currency_position'] == 'left'){
													$shipping_fee = $gtext['currency_icon'].$row->shipping_fee;
												}else{
													$shipping_fee = $row->shipping_fee.$gtext['currency_icon'];
												}
											@endphp
											<div class="checkboxlist">
												<label class="checkbox-title">
													<input data-seller_count="{{ $SellerCount }}" data-shippingfee="{{ $row->shipping_fee }}" data-total="{{ Cart::instance('shopping')->total() }}" class="shipping_method" name="shipping_method" type="radio" value="{{ $row->id }}">{{ $row->title }} : {{ $shipping_fee }}
												</label>
											</div>
										@endforeach
									</div>
								</div>
								@endif

								<h5 class="checkout-section-title">{{ __('Payment Method') }}</h5>
								<div class="checkout-payment-methods">
									<span class="text-danger error-text payment_method_error"></span>

									@if($gtext['cod_isenable'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_cod">
											<input id="payment_method_cod" name="payment_method" type="radio" value="1">
											<span class="checkout-payment-icon cod"><i class="bi bi-cash-coin"></i></span>
											<span class="checkout-payment-text">
												<strong>{{ __('Cash on Delivery') }}</strong>
												<small>{{ __('Pay when your order arrives') }}</small>
											</span>
										</label>
										<p id="pay_cod" class="checkout-payment-note hideclass">{{ $gtext['cod_description'] }}</p>
									</div>
									@endif

									@if($gtext['bank_isenable'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_bank">
											<input id="payment_method_bank" name="payment_method" type="radio" value="2">
											<span class="checkout-payment-icon bank"><i class="bi bi-bank2"></i></span>
											<span class="checkout-payment-text">
												<strong>{{ __('Bank Transfer') }}</strong>
												<small>{{ __('Secure direct bank payment') }}</small>
											</span>
										</label>
										<p id="pay_bank" class="checkout-payment-note hideclass">{{ $gtext['bank_description'] }}</p>
									</div>
									@endif

									@if($gtext['stripe_isenable'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_stripe">
											<input id="payment_method_stripe" name="payment_method" type="radio" value="3">
											<span class="checkout-payment-icon stripe"><i class="bi bi-credit-card-2-front"></i></span>
											<span class="checkout-payment-text">
												<strong>{{ __('Credit / Debit Card') }}</strong>
												<small>{{ __('Visa, Mastercard, Amex via Stripe') }}</small>
											</span>
										</label>
										<div id="pay_stripe" class="checkout-payment-note hideclass">
											<div class="mb-0">
												<div class="form-control" id="card-element"></div>
												<span class="card-errors" id="card-errors"></span>
											</div>
										</div>
									</div>
									@endif

									@if($gtext['isenable_paypal'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_paypal">
											<input id="payment_method_paypal" name="payment_method" type="radio" value="4">
											<span class="checkout-payment-icon paypal"><i class="bi bi-paypal"></i></span>
											<span class="checkout-payment-text">
												<strong>PayPal</strong>
												<small>{{ __('Fast & secure online checkout') }}</small>
											</span>
										</label>
										<p id="pay_paypal" class="checkout-payment-note hideclass">{{ __('Pay online via Paypal') }}</p>
									</div>
									@endif

									@if($gtext['isenable_razorpay'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_razorpay">
											<input id="payment_method_razorpay" name="payment_method" type="radio" value="5">
											<span class="checkout-payment-icon razorpay"><i class="bi bi-phone"></i></span>
											<span class="checkout-payment-text">
												<strong>Razorpay</strong>
												<small>{{ __('UPI, cards & wallets') }}</small>
											</span>
										</label>
										<p id="pay_razorpay" class="checkout-payment-note hideclass">{{ __('Pay online via Razorpay') }}</p>
									</div>
									@endif

									@if($gtext['isenable_mollie'] == 1)
									<div class="checkout-payment-option">
										<label class="checkout-payment-label" for="payment_method_mollie">
											<input id="payment_method_mollie" name="payment_method" type="radio" value="6">
											<span class="checkout-payment-icon mollie"><i class="bi bi-wallet2"></i></span>
											<span class="checkout-payment-text">
												<strong>Mollie</strong>
												<small>{{ __('European online payments') }}</small>
											</span>
										</label>
										<p id="pay_mollie" class="checkout-payment-note hideclass">{{ __('Pay online via Mollie') }}</p>
									</div>
									@endif
								</div>

								<input name="customer_id" type="hidden" value="@if(isset(Auth::user()->id)) {{ Auth::user()->id }} @endif" />
								<input name="razorpay_payment_id" id="razorpay_payment_id" type="hidden" />
								<a id="checkout_submit_form" href="javascript:void(0);" class="btn theme-btn mt10 checkout_btn">{{ __('Checkout') }}</a>

								@if(Session::has('pt_payment_error'))
								<div class="alert alert-danger">
									{{Session::get('pt_payment_error')}}
								</div>
								@endif
								
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</section>
	<!-- /Inner Section/ -->
</main>

@endsection

@push('scripts')
<script src="{{asset('public/frontend/js/parsley.min.js')}}"></script>
<script type="text/javascript">
var theme_color = "{{ $gtext['theme_color'] }}";
var site_name = "{{ $gtext['site_name'] }}";
var validCardNumer = 0;
var TEXT = [];
	TEXT['Please type valid card number'] = "{{ __('Please type valid card number') }}";
</script>
@if($gtext['stripe_isenable'] == 1)
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
	var isenable_stripe = "{{ $gtext['stripe_isenable'] }}";
	var stripe_key = "{{ $gtext['stripe_key'] }}";
</script>
<script src="{{asset('public/frontend/pages/payment_method.js')}}"></script>
@endif

@if($gtext['isenable_razorpay'] == 1)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
	var isenable_razorpay = "{{ $gtext['isenable_razorpay'] }}";
	var razorpay_key_id = "{{ $gtext['razorpay_key_id'] }}";
	var razorpay_currency = "{{ $gtext['razorpay_currency'] }}";
</script>
@endif
<script src="{{asset('public/frontend/pages/checkout.js')}}"></script>
@endpush	