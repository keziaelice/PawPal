<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use function Laravel\Prompts\alert;

class CartController extends Controller
{
    public function view()
    {
        $userId = Cookie::get('user');

        // Jika tidak ada di cookie, ambil dari session
        if (!$userId) {
            $user = Session::get('user');

            // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


            $userId = $user->USER_ID;
        }


        $userCart = CartController::getUserCart($userId);

        if ($userCart->isEmpty()) {
            return redirect()->route('empty.cart');
        }
        else{
            return view("cart",["datacart" => $userCart]);
        }

    }
    public function getUserCart(string $id){
        // $userid = DB::table('CART')
        // ->join('PRODUK','PRODUK.PRODUCT_ID','=','CART.PRODUCT_ID')
        // ->select('CART.PRODUCT_ID','PRODUK.PRODUCT_NAME','PRODUK.PRODUCT_PRICE','CART.CART_QTY','CART.CART_QTY * PRODUK.PRODUCT_PRICE AS SUBTOTAL')
        // ->where('CART.USER_ID','=',$id);
        // return $userid;
        $userCart = DB::table('CART')
        ->join('PRODUK','PRODUK.PRODUCT_ID','=','CART.PRODUCT_ID')
        ->select('CART.PRODUCT_ID','PRODUK.PRODUCT_NAME','PRODUK.PRODUCT_PRICE','CART.CART_QTY',DB::raw('CART.CART_QTY * PRODUK.PRODUCT_PRICE AS SUBTOTAL'))
        ->where('CART.USER_ID','=',$id)
        ->where('CART.STATUS_DEL', '=',0)
        ->get(); // Execute the query and get results

    foreach ($userCart as $item) {
        $productId = $item->PRODUCT_ID; // Access PRODUCT_ID from each item
        // Use $productId and other properties from $item
    }

    return $userCart;
    }
    public function updateCart(Request $request)
    {
    $products = $request->input('products');
    $userId = Cookie::get('user');

    // Jika tidak ada di cookie, ambil dari session
    if (!$userId) {
        $user = Session::get('user');

        // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


        $userId = $user->USER_ID;
    }
    // Update the Cart column in the database
    foreach ($products as $product) {
        DB::table('CART')
        ->where('USER_ID',$userId)
        ->where('PRODUCT_ID',$product['productId'])
        ->update(['CART_QTY' => $product['newQty']]);
    }
    return response()->json(['success' => true]);
}
public function addCart(Request $request)
{
    // dd($request);
    $id = $request->input('id');
    alert($id);
    $userId = Cookie::get('user');

    // If not in cookie, get from session
    if (!$userId) {
        $user = Session::get('user');
        $userId = $user->USER_ID;
    }

    // Check if the product is already in the cart
    $cartItem = DB::table('CART')
        ->where('USER_ID', '=', $userId)
        ->where('PRODUCT_ID', '=', $id)
        ->where('STATUS_DEL', '=', 0)
        ->first();

    if($cartItem) {
        // If the product is already in the cart, increase the quantity
        $newQty = $cartItem->CART_QTY + 1;
        $update = DB::table('CART')
            ->where('PRODUCT_ID', $id)
            ->where('USER_ID', $userId)
            ->update(['CART_QTY' => $newQty]);

        if ($update) {
            return response()->json(['success' => true, 'message' => 'Item quantity updated in cart']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error updating cart item quantity']);
        }
    } else {
        // If the product is not in the cart, add it
        $insert = DB::table('CART')
            ->insert([
                'PRODUCT_ID' => $id,
                'CART_QTY' => 1,
                'USER_ID' => $userId,
                'STATUS_DEL' => 0
            ]);

        if ($insert) {
            return response()->json(['success' => true, 'message' => 'Item added to cart successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error adding item to cart']);
        }
    }
}
public function deleteCart(){
    $userId = Cookie::get('user');

    // Jika tidak ada di cookie, ambil dari session
    if (!$userId) {
        $user = Session::get('user');

        // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


        $userId = $user->USER_ID;
    }
    $update = DB::table('CART')
            ->where('USER_ID', $userId)
            ->update(['STATUS_DEL'=>1]);

        if ($update) {
            return response()->json(['success' => true, 'message' => 'Item quantity updated in cart']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error updating cart item quantity']);
        }
}
public function deleteItem(Request $request){
    $userId = Cookie::get('user');

    // Jika tidak ada di cookie, ambil dari session
    if (!$userId) {
        $user = Session::get('user');

        // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


        $userId = $user->USER_ID;
    }
    $id = $request->input('produkid');
    $update = DB::table('CART')
    ->where('USER_ID', $userId)
    ->where('PRODUCT_ID',$id)
    ->update(['STATUS_DEL'=>1]);
}

}

// namespace App\Http\Controllers;
// use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\Session;
// use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Http\Request;

// use function Laravel\Prompts\alert;

// class CartController extends Controller
// {
//     public function view()
//     {
//         $userId = Session::get('user');
//         // dd($userId);
//         $iniuser = $userId->USER_ID;

//         if (!$userId) {
//             $user = Session::get('user');

//             if (!$user) {
//                 return redirect()->route('login')->withErrors(['error' => 'Please log in to access your cart.']);
//             }


//             // $userId = $user->USER_ID;
//         }

//         $userCart = DB::table('CART')->where('CART.USER_ID','=',$iniuser)->get();
//         dd($userCart);

//         // dd($userId);


//         // $userCart = CartController::getUserCart($userId, );
//         // dd($userCart);

//         // if ($userCart->isEmpty()) {
//         //     return redirect('/empty-cart');
//         // } else {
//         //     return view("cart", ["datacart" => $userCart]);
//         // }


//     }
//     public function getUserCart(string $id, string $productId){

//         // $userid = DB::table('CART')
//         // ->join('PRODUK','PRODUK.PRODUCT_ID','=','CART.PRODUCT_ID')
//         // ->select('CART.PRODUCT_ID','PRODUK.PRODUCT_NAME','PRODUK.PRODUCT_PRICE','CART.CART_QTY','CART.CART_QTY * PRODUK.PRODUCT_PRICE AS SUBTOTAL')
//         // ->where('CART.USER_ID','=',$id);
//         // return $userid;
//         $userCart = DB::table('CART')
//         ->join('PRODUK','PRODUK.PRODUCT_ID','=','CART.PRODUCT_ID')
//         ->select('CART.PRODUCT_ID','PRODUK.PRODUCT_NAME','PRODUK.PRODUCT_PRICE','CART.CART_QTY',DB::raw('CART.CART_QTY * PRODUK.PRODUCT_PRICE AS SUBTOTAL'))
//         ->where('CART.USER_ID','=',$id)
//         ->where('CART.PRODUCT_ID', '=', $productId)
//         ->where('CART.STATUS_DEL', '=',0)
//         ->get(); // Execute the query and get results


//         // foreach ($userCart as $item) {
//         //     // dd($item->CART_QTY);
//         //     $productId = $item->PRODUCT_ID; // Access PRODUCT_ID from each item
//         //     // Use $productId and other properties from $item
//         // }

//         // dd($userCart);
//         return $userCart;
//     }

//     public function updateCart(Request $request)
//     {
//         $products = $request->input('products');
//         $userId = Session::get('user');

//         // Jika tidak ada di cookie, ambil dari session
//         if (!$userId) {
//             $user = Session::get('user');

//             // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


//             $userId = $user->USER_ID;
//         }
//         // Update the Cart column in the database
//         foreach ($products as $product) {
//             DB::table('CART')
//             ->where('USER_ID',$userId)
//             ->where('PRODUCT_ID',$product['productId'])
//             ->update(['CART_QTY' => $product['newQty']]);
//         }
//         return response()->json(['success' => true]);
//     }

//     public function addCart(Request $request)
//     {



//         // dd($request);
//         $id = $request->productId;
//         // dd($id);
//         $userId = Session::get('user');
//         // dd($userId);
//         $user = $userId->USER_ID;
//         $datacart = CartController::getUserCart($user, $id);
//         // dd($userId);

//         // If not in cookie, get from session
//         if (!$userId) {
//             $user = Session::get('user');
//             $userId = $user->USER_ID;
//         }

//         $cartItem=[];
//         // Check if the product is already in the cart
//         $cartItem = DB::table('CART')
//             ->where('USER_ID', '=', $userId)
//             ->where('PRODUCT_ID', '=', $id)
//             ->where('STATUS_DEL', '=', 0)
//             ->get();

//         if($cartItem) {
//             // If the product is already in the cart, increase the quantity
//             $newQty = $cartItem->CART_QTY + 1;
//             $update = DB::table('CART')
//                 ->where('PRODUCT_ID', $id)
//                 ->where('USER_ID', $userId)
//                 ->update(['CART_QTY' => $newQty]);

//             if ($update) {
//                 return view('cart')->with(['success' => true, 'message' => 'Item quantity updated in cart']);
//             } else {
//                 return view('cart')->with(['success' => false, 'message' => 'Error updating cart item quantity']);
//             }
//         }
//         else {
//             // If the product is not in the cart, add it
//             $insert = DB::table('CART')
//                 ->insert([
//                     'PRODUCT_ID' => $id,
//                     'CART_QTY' => 1,
//                     'USER_ID' => $userId,
//                     'STATUS_DEL' => 0
//                 ]);

//             if ($insert) {
//                 return view('cart', compact('datacart'));
//             } else {
//                 return view('cart', compact('datacart'));
//             }
//         }
//     }

//     public function deleteCart(){
//         $userId = Cookie::get('user');

//         // Jika tidak ada di cookie, ambil dari session
//         if (!$userId) {
//             $user = Session::get('user');

//             // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


//             $userId = $user->USER_ID;
//         }
//         $update = DB::table('CART')
//                 ->where('USER_ID', $userId)
//                 ->update(['STATUS_DEL'=>1]);

//             if ($update) {
//                 return response()->json(['success' => true, 'message' => 'Item quantity updated in cart']);
//             } else {
//                 return response()->json(['success' => false, 'message' => 'Error updating cart item quantity']);
//             }
//     }

//     public function deleteItem(Request $request){
//         $userId = Cookie::get('user');

//         // Jika tidak ada di cookie, ambil dari session
//         if (!$userId) {
//             $user = Session::get('user');

//             // Jika tidak ada user di session, redirect ke halaman login atau lakukan tindakan lain


//             $userId = $user->USER_ID;
//         }
//         $id = $request->input('produkid');
//         $update = DB::table('CART')
//         ->where('USER_ID', $userId)
//         ->where('PRODUCT_ID',$id)
//         ->update(['STATUS_DEL'=>1]);
//     }

// }
