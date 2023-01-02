<?php

namespace App\Http\Controllers\Customer;

use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Variant;
use App\Services\FlashNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController
{
	public function index(Request $request)
	{
		// TODO: Add pagination
		$carts = Cart::query()
						  ->where('user_id', $request->user()->id)
			  ->with('variant', 'product')
						  ->get();

		return Inertia::render('Customer/Carts/Index', [
			'carts' => CartResource::collection($carts),
		]);
	}

  public function store(Request $request)
  {
  	$variant = Variant::findOrFail($request->variantId);

  	$request->validate([
  		'quantity' => 'required|integer|min:1|max:'.$variant->stock,
  	]);

  	// updateOrCreate
  	Cart::updateOrCreate([
  		'user_id' => $request->user()->id,
  		'variant_id' => $variant->id,
  		'product_id' => $variant->product_id,
  	], [
  		'quantity' => $request->quantity,
  	]);

  	(new FlashNotification())->create('Cart');

  	return redirect()->route('customer.carts.index');
  }

  public function update(Request $request, Cart $cart)
  {
  	$request->validate([
  		'quantity' => 'required|integer|min:1|max:'.$cart->variant->stock,
  	]);

  	$cart->update([
  		'quantity' => $request->quantity,
  	]);

  	(new FlashNotification())->update('Cart qty.');

  	return redirect()->back();
  }

  public function destroy(Cart $cart)
  {
  	$cart->delete();

  	(new FlashNotification())->destroy('Cart');

  	return redirect()->back();
  }

  public function checkout() {
    session()->flash('success', 'Successfully checkout!');

  	return redirect()->back();
  }
}
