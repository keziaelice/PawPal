<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

use function Laravel\Prompts\alert;

class WishlistController extends Controller
{
    public function view(Request $request)
    {
        $userId = Cookie::get('user');

        if (!$userId) {
            $user = Session::get('user');

            if (!$user) {
                return redirect()->route('login')->withErrors(['error' => 'Please log in to access your wishlist.']);
            }

            $userId = $user->USER_ID;
        }

        $products = DB::table('PRODUK as P')
            ->join('WISHLIST as W', 'W.PRODUCT_ID', '=', 'P.PRODUCT_ID')
            ->where('W.USER_ID', $userId)
            ->where('W.STATUS_DEL', 0)
            ->select('P.PRODUCT_ID', 'P.PRODUCT_NAME', 'P.PRODUCT_PRICE')
            ->get();

            if ($products->isEmpty()) {
                return redirect()->route('wishlist.empty');
            }
            else{
                return view('wishlist', compact('products'));
            }
    }

    public function store(Request $request, $productId)
    {

        $userId = Cookie::get('user');
        dd($userId);
        if (!$userId) {
            $user = Session::get('user');
            if (!$user) {
                return redirect()->route('login')->withErrors(['error' => 'Please log in to access your wishlist.']);
            }
            $userId = $user->USER_ID;
        }

        $existingWishlistItem = DB::table('WISHLIST')
            ->where('PRODUCT_ID', $productId)
            ->where('USER_ID', $userId)
            ->first();

        if (!$existingWishlistItem) {
            DB::table('WISHLIST')->insert([
                'PRODUCT_ID' => $productId,
                'USER_ID' => $userId,
                'STATUS_DEL' => 0
            ]);
        }

        return redirect()->route('wishlist.view')->with('success', 'Product added to wishlist!');
    }

    public function destroy($productId)
    {
        $userId = Cookie::get('user');
        if (!$userId) {
            $user = Session::get('user');
            if (!$user) {
                return redirect()->route('login')->withErrors(['error' => 'Please log in to access your wishlist.']);
            }
            $userId = $user->USER_ID;
        }

        DB::table('WISHLIST')
            ->where('PRODUCT_ID', $productId)
            ->where('USER_ID', $userId)
            ->update(['STATUS_DEL' => 1]);

        return redirect()->route('wishlist.view')->with('success', 'Product removed from wishlist!');
    }

    public function addWishlist(Request $request)
{
    $id = $request->input('id');
    $userId = Cookie::get('user');

    // If not in cookie, get from session
    if (!$userId) {
        $user = Session::get('user');
        $userId = $user->USER_ID;
    }

    $existingWishlistItem = DB::table('WISHLIST')
        ->where('PRODUCT_ID', $id)
        ->where('USER_ID', $userId)
        ->first();

    if (!$existingWishlistItem) {
        $insert = DB::table('WISHLIST')
        ->insert([
            'USER_ID' => $userId,
            'PRODUCT_ID' => $id,
            'STATUS_DEL' => 0
        ]);
        if ($insert) {
            return response()->json(['success' => true, 'message' => 'Item added to wishlist successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error adding item to wishlist']);
        }
    }

    return redirect()->route('wishlist.view');


}
}

