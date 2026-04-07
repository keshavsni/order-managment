<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function index(Request $request){

        $query = Order::with(['items.product','customer:id,name,email']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10);

        return OrderResource::collection($orders)->additional(['status' => true]);
    }
    public function store(OrderRequest $request){

        return DB::transaction(function () use ($request) {

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'status' => 'pending'
            ]);

            $total = 0;

            foreach ($request->items as $item) {

                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock");
                }

                $product->decrement('stock', $item['quantity']);

                $price = $product->price * $item['quantity'];

                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ]);

                $total += $price;
            }

            $order->update(['total_amount' => $total]);

            ProcessOrderJob::dispatch($order);

            return response()->json([
                'status' => true,
                'data' => new OrderResource($order->load(['items.product','customer:id,name,email']))
            ], 201);
        });
    }
}
