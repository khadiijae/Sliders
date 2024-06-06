<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function userDetails()
    {

        try {
            try {
                //authenticate user with tokn
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                $user = null;
            }

            if ($user) {
                $id = $user->id;
                Log::info($id);
            }
            // $userCartItems = $user->cartItems()->get();

            //  show cartItems with product detals
            $cartItems = Cart::where('customer_id', $id)->with(['product' => function ($query) {
                $query->select('id', 'name', 'price');
            }])->get();

            //  user's orders+ orderItems + productDetails
            $orders = Order::where('customer_id', $id)
                ->with(['orderLineItems.product' => function ($query) {
                    $query->select('id', 'name', 'price');
                }])
                ->get();

            return response()->json([
                'user' => $user,
                'cartItems' => $cartItems,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {

            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }
}
