<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\PayRequest;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Payment\PaymentService;
use App\Services\Payment\Requests\IDPayRequest;
use Illuminate\Support\Facades\Cookie;

class PaymentController extends Controller
{
    public function pay(PayRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::firstOrCreate([
            'email'  => $validatedData['email']
        ],[
            'name'   => $validatedData['name'],
            'mobile' => $validatedData['mobile'],
            'email'  => $validatedData['email']
        ]);

        try{

            $orderItems =json_decode(Cookie::get('basket'),true);

            $products =  Product::findMany(array_keys($orderItems));

            $productsPrice = $products->sum('price');

            // dd($productsPrice);

            $refCode = Str::random(30);


            $createdOrder = Order::create([
                'amount'  => $productsPrice,
                'ref_code'=> $refCode,
                'status'  => 'unpaid',
                'user_id' => $user->id
            ]);

            $orderItemsForCreatedOrder = $products->map(function($product){
                $currentProduct = $product->only('price','id');

                $currentProduct['product_id'] = $currentProduct['id'];
                unset($currentProduct['id']);

                return $currentProduct ;
            });

            $createdOrder->orderItems()->createMany($orderItemsForCreatedOrder->toArray());

            $refId=rand(1111,9999);

            $createdPayment = Payment::create([
                'gateway'  => 'idpay',
                'ref_id'   => $refId ,
                'res_id'   => $refId ,
                'status'   => 'unpaid',
                'order_id' => $createdOrder->id
            ]);

            $idPayRequest = new IDPayRequest([
                'amount' =>$productsPrice,
                'user' =>$user,
                'orderId'  =>$refCode,
            ]);

            $paymentService = new PaymentService(PaymentService::IDPAY , $idPayRequest);
            $paymentService->pay();

            return $paymentService->pay();

        }catch(\Exception $e){
            return back()->with('failed',$e->getMessage());
        }

    }

    public function callback()
    {

    }
}
