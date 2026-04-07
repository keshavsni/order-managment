<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function create(CustomerRequest $request){

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json([
            'status' => true,
            'data' => $customer
        ],201);
    }

    public function customerOrders($id)
    {
        $customer = Customer::find($id);

        if(!$customer){
            return response()->json([
                'status' => false,
                'message' => "Invalid customer id"
            ],400);
        }
        $orders = Order::with(['items.product','customer:id,name,email'])
            ->where('customer_id', $id)
            ->get();

        return OrderResource::collection($orders)->additional(['status' => true]);    
    }
}
