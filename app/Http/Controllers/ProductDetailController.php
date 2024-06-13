<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailController extends Controller
{
    public function view($productId)
    {
        // Ambil detail produk
        $product = DB::table('PRODUK')->where('PRODUCT_ID', $productId)->first();

        // Ambil kategori produk
        $category = DB::table('PRODUK')
            ->join('CATEGORY', 'CATEGORY.CATEGORY_ID', '=', 'PRODUK.CATEGORY_ID')
            ->where('PRODUK.PRODUCT_ID', $productId)
            ->select('PRODUK.*', 'CATEGORY.CATEGORY_NAME', 'CATEGORY.CATEGORY_ANIMAL')
            ->first();

        // Ambil produk terkait berdasarkan CATEGORY_ANIMAL yang sama, kecuali produk yang sedang dilihat
        $relatedProducts = DB::table('PRODUK')
            ->join('CATEGORY', 'CATEGORY.CATEGORY_ID', '=', 'PRODUK.CATEGORY_ID')
            ->where('CATEGORY.CATEGORY_ANIMAL', $category->CATEGORY_ANIMAL)
            ->where('PRODUK.PRODUCT_ID', '!=', $productId)
            ->inRandomOrder()
            ->take(6)
            ->get();

        // Kirim data ke view
        return view("single-product-variable", compact('product', 'category', 'relatedProducts'));
    }
}
