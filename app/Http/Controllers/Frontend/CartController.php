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
	public function AddToCart($id, $qty){

		$res = array();
		$datalist = Product::where('id', $id)->first();
		$user = User::where('id', $datalist['user_id'])->first();

		$data = array();
		$data['id'] = $datalist['id'];
		$data['name'] = $datalist['title'];
		$data['qty'] = $qty == 0 ? 1 : $qty;
		$data['price'] = $datalist['sale_price'];
		$data['weight'] = 0;
		$data['options'] = array();
		$data['options']['thumbnail'] = $datalist['f_thumbnail'];
		$data['options']['unit'] = $datalist['variation_size'];
		$data['options']['seller_id'] = $datalist['user_id'];
		$data['options']['seller_name'] = $user['name'];
		$data['options']['store_name'] = $user['shop_name'];
		$data['options']['store_logo'] = $user['photo'];
		$data['options']['store_url'] = $user['shop_url'];
		$data['options']['seller_email'] = $user['email'];
		$data['options']['seller_phone'] = $user['phone'];
		$data['options']['seller_address'] = $user['address'];

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

			if($gtext['currency_position'] == 'left'){
				$price = '<span id="product-quatity">'.$row->qty.'</span> x '.$gtext['currency_icon'].$row->price; 
			}else{
				$price = '<span id="product-quatity">'.$row->qty.'</span> x '.$row->price.$gtext['currency_icon']; 
			}
		
			$items .= '<li>
						<div class="cart-item-card">
							<a data-id="'.$row->rowId.'" id="removetocart_'.$row->id.'" onclick="onRemoveToCart('.$row->id.')" href="javascript:void(0);" class="item-remove"><i class="bi bi-x"></i></a>
							<div class="cart-item-img">
								<img src="'.$Path.'/'.$row->options->thumbnail.'" alt="'.$row->name.'" />
							</div>
							<div class="cart-item-desc">
								<h6><a href="'.route('frontend.product', [$row->id, str_slug($row->name)]).'">'.$row->name.'</a></h6>
								<p>'.$price.'</p>
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
		$user = User::where('id', $datalist['user_id'])->first();
		
		$data = array();
		$data['id'] = $datalist['id'];
		$data['name'] = $datalist['title'];
		$data['qty'] = 1;
		$data['price'] = $datalist['sale_price'];
		$data['weight'] = 0;
		$data['options'] = array();
		$data['options']['thumbnail'] = $datalist['f_thumbnail'];

		$data['options']['seller_id'] = $datalist['user_id'];
		$data['options']['seller_name'] = $user['name'];
		$data['options']['store_name'] = $user['shop_name'];
		$data['options']['store_logo'] = $user['photo'];
		$data['options']['store_url'] = $user['shop_url'];
		$data['options']['seller_email'] = $user['email'];
		$data['options']['seller_phone'] = $user['phone'];
		$data['options']['seller_address'] = $user['address'];		
		
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
