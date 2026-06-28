<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use Cart;

class CartController extends Controller
{
	//Add to Cart
	public function AddToCart(Request $request, $id, $qty){

		$res = array();
		$datalist = Product::where('id', $id)->first();
		$user = $datalist['user_id'] > 0 ? User::where('id', $datalist['user_id'])->first() : null;

		$unit = strtolower($request->query('unit', 'piece'));
		if ($unit === 'box') {
			$price = $datalist['box_price'];
			$unitLabel = 'Box';
		} else {
			$price = $datalist['piece_price'] ?? $datalist['sale_price'];
			$unitLabel = 'Piece';
			$unit = 'piece';
		}

		// Resolve selected shade (optional) and its price server-side
		$selectedShade = trim((string) $request->query('shade', ''));
		$shadeName = '';
		$shadeColor = '';
		$shadeKey = '';
		if ($selectedShade !== '') {
			$shades = $datalist['shades'];
			if (is_string($shades)) {
				$shades = json_decode($shades, true);
			}
			if (is_array($shades)) {
				foreach ($shades as $shade) {
					if (isset($shade['name']) && $shade['name'] === $selectedShade) {
						$shadeName = $shade['name'];
						$shadeColor = $shade['color'] ?? '';
						$shadeKey = \Illuminate\Support\Str::slug($shade['name']);
						if (isset($shade['price']) && $shade['price'] !== null && $shade['price'] !== '') {
							$price = $shade['price'];
						}
						break;
					}
				}
			}
		}

		if ($price === null || $price === '') {
			$res['msgType'] = 'error';
			$res['msg'] = __('Price is not available for the selected unit.');
			return response()->json($res);
		}

		$data = array();
		$data['id'] = $datalist['id'] . '-' . $unit . ($shadeKey !== '' ? '-' . $shadeKey : '');
		$data['name'] = $datalist['title'];
		$data['qty'] = $qty == 0 ? 1 : $qty;
		$data['price'] = $price;
		$data['weight'] = 0;
		$data['options'] = array();
		$data['options']['product_id'] = $datalist['id'];
		$data['options']['thumbnail'] = $datalist['f_thumbnail'];
		$data['options']['unit'] = $unitLabel;
		if ($shadeName !== '') {
			$data['options']['shade'] = $shadeName;
			$data['options']['shade_color'] = $shadeColor;
		}
		if ($unit === 'box' && !empty($datalist['pieces_per_box'])) {
			$data['options']['pieces_per_box'] = $datalist['pieces_per_box'];
		}
		$data['options']['seller_id'] = $datalist['user_id'];
		$data['options']['seller_name'] = $user ? $user['name'] : '';
		$data['options']['store_name'] = $user ? $user['shop_name'] : '';
		$data['options']['store_logo'] = $user ? $user['photo'] : '';
		$data['options']['store_url'] = $user ? $user['shop_url'] : '';
		$data['options']['seller_email'] = $user ? $user['email'] : '';
		$data['options']['seller_phone'] = $user ? $user['phone'] : '';
		$data['options']['seller_address'] = $user ? $user['address'] : '';

		$response = Cart::instance('shopping')->add($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('New Data Added Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Added product to cart failed.');
		}
		
		return response()->json($res);
	}
	
	//Add to Cart
	public function ViewCart(){
		$gtext = gtext();
		$gtax = getTax();
		$Path = asset('public/media');
		
		$data = Cart::instance('shopping')->content();
		
		$tax_rate = $gtax['percentage'];
		config(['cart.tax' => $tax_rate]);
		
		$items = '';
		foreach ($data as $key => $row) {
			
			$row->setTaxRate($tax_rate);
			Cart::instance('shopping')->update($row->rowId, $row->qty);

			$productId = $row->options->product_id ?? $row->id;
			$unitDisplay = $row->options->unit;
			if ($row->options->unit === 'Box' && !empty($row->options->pieces_per_box)) {
				$unitDisplay .= ' ('.$row->options->pieces_per_box.' pcs)';
			}

			$product = Product::find($productId);
			$stockQty = ($product && $product->is_stock == 1 && $product->stock_status_id == 1)
				? (int) $product->stock_qty
				: 999;

			if($gtext['currency_position'] == 'left'){
				$unitPrice = $gtext['currency_icon'].$row->price;
			}else{
				$unitPrice = $row->price.$gtext['currency_icon'];
			}

			$qtyControls = '<div class="cart-qty-wrap mini-cart-qty">'
				.'<button type="button" class="mini-qty-btn cart-qty-minus" data-rowid="'.$row->rowId.'" data-qty="'.$row->qty.'" data-stockqty="'.$stockQty.'">-</button>'
				.'<span class="mini-qty-val cart-qty-display">'.$row->qty.'</span>'
				.'<button type="button" class="mini-qty-btn cart-qty-plus" data-rowid="'.$row->rowId.'" data-qty="'.$row->qty.'" data-stockqty="'.$stockQty.'">+</button>'
				.'</div>';
		
			$items .= '<li>
						<div class="cart-item-card">
							<a data-id="'.$row->rowId.'" id="removetocart_'.$row->rowId.'" onclick="onRemoveToCart(\''.$row->rowId.'\')" href="javascript:void(0);" class="item-remove"><i class="bi bi-x"></i></a>
							<div class="cart-item-img">
								<img src="'.$Path.'/'.$row->options->thumbnail.'" alt="'.$row->name.'" />
							</div>
							<div class="cart-item-desc">
								<h6><a href="'.route('frontend.product', [$productId, str_slug($row->name)]).'">'.$row->name.'</a></h6>
								<p><span class="mini-cart-price">'.$unitPrice.'</span> ('.$unitDisplay.')</p>
								'.$qtyControls.'
							</div>
						</div>
					</li>';
		}
		
		$count = Cart::instance('shopping')->count();
		$subtotal = Cart::instance('shopping')->subtotal();
		$tax = Cart::instance('shopping')->tax();
		$priceTotal = Cart::instance('shopping')->priceTotal();
		$total = Cart::instance('shopping')->total();
		
		$datalist = array();
		$datalist['items'] = $items;
		$datalist['total_qty'] = $count;
		if($gtext['currency_position'] == 'left'){
			$datalist['sub_total'] = $gtext['currency_icon'].$subtotal;
			$datalist['tax'] = $gtext['currency_icon'].$tax;
			$datalist['price_total'] = $gtext['currency_icon'].$priceTotal;
			$datalist['total'] = $gtext['currency_icon'].$total;
		}else{
			$datalist['sub_total'] = $subtotal.$gtext['currency_icon'];
			$datalist['tax'] = $tax.$gtext['currency_icon'];
			$datalist['price_total'] = $priceTotal.$gtext['currency_icon'];
			$datalist['total'] = $total.$gtext['currency_icon'];
		}

		return response()->json($datalist);
	}

	//Update Cart quantity
	public function UpdateCart($rowid, $qty){
		$res = array();
		$gtext = gtext();
		$gtax = getTax();

		$item = Cart::instance('shopping')->get($rowid);
		if (!$item) {
			$res['msgType'] = 'error';
			$res['msg'] = __('Item not found in cart.');
			return response()->json($res);
		}

		$qty = (int) $qty;

		if ($qty <= 0) {
			Cart::instance('shopping')->remove($rowid);
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Removed Successfully');
			$res['removed'] = true;
		} else {
			$productId = $item->options->product_id ?? explode('-', $item->id)[0];
			$product = Product::find($productId);

			if ($product && $product->is_stock == 1) {
				if ($product->stock_status_id != 1) {
					$res['msgType'] = 'error';
					$res['msg'] = __('This product out of stock.');
					return response()->json($res);
				}
				if ($qty > $product->stock_qty) {
					$res['msgType'] = 'error';
					$res['msg'] = __('The value must be less than or equal to').' '.$product->stock_qty;
					return response()->json($res);
				}
			}

			Cart::instance('shopping')->update($rowid, $qty);
			$res['msgType'] = 'success';
			$res['msg'] = __('Cart updated successfully.');
			$res['removed'] = false;
			$res['qty'] = $qty;

			$updated = Cart::instance('shopping')->get($rowid);
			$lineTotal = $updated->price * $qty;
			if ($gtext['currency_position'] == 'left') {
				$res['line_total'] = $gtext['currency_icon'].number_format($lineTotal, 2);
				$res['line_sub_price'] = $gtext['currency_icon'].number_format($updated->price, 2).' x '.$qty;
			} else {
				$res['line_total'] = number_format($lineTotal, 2).$gtext['currency_icon'];
				$res['line_sub_price'] = number_format($updated->price, 2).$gtext['currency_icon'].' x '.$qty;
			}
		}

		$tax_rate = $gtax['percentage'];
		config(['cart.tax' => $tax_rate]);

		foreach (Cart::instance('shopping')->content() as $row) {
			$row->setTaxRate($tax_rate);
			Cart::instance('shopping')->update($row->rowId, $row->qty);
		}

		$count = Cart::instance('shopping')->count();
		$subtotal = Cart::instance('shopping')->subtotal();
		$tax = Cart::instance('shopping')->tax();
		$priceTotal = Cart::instance('shopping')->priceTotal();
		$total = Cart::instance('shopping')->total();

		$res['total_qty'] = $count;
		$res['cart_total_number'] = $total;
		if ($gtext['currency_position'] == 'left') {
			$res['sub_total'] = $gtext['currency_icon'].$subtotal;
			$res['tax'] = $gtext['currency_icon'].$tax;
			$res['price_total'] = $gtext['currency_icon'].$priceTotal;
			$res['total'] = $gtext['currency_icon'].$total;
		} else {
			$res['sub_total'] = $subtotal.$gtext['currency_icon'];
			$res['tax'] = $tax.$gtext['currency_icon'];
			$res['price_total'] = $priceTotal.$gtext['currency_icon'];
			$res['total'] = $total.$gtext['currency_icon'];
		}

		return response()->json($res);
	}
	
	//Remove to Cart
	public function RemoveToCart($rowid){
		$res = array();

		$response = Cart::instance('shopping')->remove($rowid);

		if($response == ''){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Removed Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data remove failed');
		}
		
		return response()->json($res);
	}
	
    //get Cart
    public function getCart(){
        return view('frontend.cart');
    }
	
    //get Cart
    public function getViewCartData(){
		$gtext = gtext();
		$gtax = getTax();

		$data = Cart::instance('shopping')->content();

		$tax_rate = $gtax['percentage'];
		config(['cart.tax' => $tax_rate]);

		foreach ($data as $key => $row) {
			$row->setTaxRate($tax_rate);
			Cart::instance('shopping')->update($row->rowId, $row->qty);
		}
		
		$count = Cart::instance('shopping')->count();
		$subtotal = Cart::instance('shopping')->subtotal();
		$tax = Cart::instance('shopping')->tax();
		$priceTotal = Cart::instance('shopping')->priceTotal();
		$total = Cart::instance('shopping')->total();
		$discount = Cart::instance('shopping')->discount();
		
		$datalist = array();
		$datalist['total_qty'] = $count;
		if($gtext['currency_position'] == 'left'){
			$datalist['sub_total'] = $gtext['currency_icon'].$subtotal;
			$datalist['tax'] = $gtext['currency_icon'].$tax;
			$datalist['price_total'] = $gtext['currency_icon'].$priceTotal;
			$datalist['total'] = $gtext['currency_icon'].$total;
			$datalist['discount'] = $gtext['currency_icon'].$discount;
		}else{
			$datalist['sub_total'] = $subtotal.$gtext['currency_icon'];
			$datalist['tax'] = $tax.$gtext['currency_icon'];
			$datalist['price_total'] = $priceTotal.$gtext['currency_icon'];
			$datalist['total'] = $total.$gtext['currency_icon'];
			$datalist['discount'] = $discount.$gtext['currency_icon'];
		}

		return response()->json($datalist);
    }
	
	//Add to Wishlist
	public function addToWishlist($id){

		$res = array();
		$datalist = Product::where('id', $id)->first();
		$user = $datalist['user_id'] > 0 ? User::where('id', $datalist['user_id'])->first() : null;
		
		$data = array();
		$data['id'] = $datalist['id'];
		$data['name'] = $datalist['title'];
		$data['qty'] = 1;
		$data['price'] = $datalist['piece_price'] ?? $datalist['sale_price'] ?? $datalist['box_price'];
		$data['weight'] = 0;
		$data['options'] = array();
		$data['options']['thumbnail'] = $datalist['f_thumbnail'];

		$data['options']['seller_id'] = $datalist['user_id'];
		$data['options']['seller_name'] = $user ? $user['name'] : '';
		$data['options']['store_name'] = $user ? $user['shop_name'] : '';
		$data['options']['store_logo'] = $user ? $user['photo'] : '';
		$data['options']['store_url'] = $user ? $user['shop_url'] : '';
		$data['options']['seller_email'] = $user ? $user['email'] : '';
		$data['options']['seller_phone'] = $user ? $user['phone'] : '';
		$data['options']['seller_address'] = $user ? $user['address'] : '';		
		
		$response = Cart::instance('wishlist')->add($data);
		if($response){
			$res['msgType'] = 'success';
			$res['msg'] = __('New Data Added Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Added product to wishlist failed.');
		}
		
		return response()->json($res);
	}
	
    //get Wishlist
    public function getWishlist(){
		return view('frontend.wishlist');
	}
	
	//Remove to Wishlist
	public function RemoveToWishlist($rowid){
		$res = array();

		$response = Cart::instance('wishlist')->remove($rowid);

		if($response == ''){
			$res['msgType'] = 'success';
			$res['msg'] = __('Data Removed Successfully');
		}else{
			$res['msgType'] = 'error';
			$res['msg'] = __('Data remove failed');
		}
		
		return response()->json($res);
	}
	
	//Count to Wishlist
	public function countWishlist(){

		$count = Cart::instance('wishlist')->content()->count();
		
		return response()->json($count);
	}
}
