<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    
     public function index(Request $request)
    {
        $query = Product::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return Cache::remember('products_'.$request->search, 60, fn() => ProductResource::collection($query->get())->additional(['status' => true]));
    }
    public function create(ProductRequest $request){
        
       $product = Product::create($request->all());

       return response()->json([
        "status" => true,
        "data" => new ProductResource($product)
       ],201);
    }

}
