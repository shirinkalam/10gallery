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
use App\Services\Payment\Requests\IDPayVerifyRequest;
use App\Mail\SendOrderedImages;
use Illuminate\Support\Facades\Mail;

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

            if(count($orderItems) <= 0){
                throw new \InvalidArgumentException('سبد خرید شما خالی است');
            }

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


            $createdPayment = Payment::create([
                'gateway'  => 'idpay',
                'ref_code'   => $refCode ,
                'status'   => 'unpaid',
                'order_id' => $createdOrder->id
            ]);

            $idPayRequest = new IDPayRequest([
                'amount' =>$productsPrice,
                'user' =>$user,
                'orderId'  =>$refCode,
                'apiKey' => config('services.gateways.id_pay.api_key'),
            ]);

            $paymentService = new PaymentService(PaymentService::IDPAY , $idPayRequest);

            return $paymentService->pay();

        }catch(\Exception $e){
            return back()->with('failed',$e->getMessage());
        }

    }

    public function callback(Request $request)
    {
        $paymentInfo = $request->all();

        $idPayVaerifyRequest =new IDPayVerifyRequest([
            'id' => $paymentInfo['id'],
            'orderId' => $paymentInfo['order_id'],
            'apiKey' => config('services.gateways.id_pay.api_key')
        ]);

        $paymentService = new PaymentService(PaymentService::IDPAY , $idPayVaerifyRequest);

        $result = $paymentService->verify();

        if(!$result['status']){
            return redirect()->route('home.checkout')->with('failed','پرداخت شما انجام نشد');
        }

        if($result['statusCode'] == 101){
            return redirect()->route('home.checkout')->with('failed','پرداخت شما قبلا انجام شده و تصاویر برای شما ارسال شده اند');

        }

        $currentPayment = Payment::where('ref_code' , $result['data']['order_id'])->first();

        $currentPayment->update([
            'status'   =>'paied',
            'res_id' => $result['data']['track_id']
        ]);

        $currentPayment->order()->update([
            'status'   =>'paid',
        ]);

        $currentUser = $currentPayment->order->user;

        $reservedImages = $currentPayment->order->orderItems->map(function($orderItem){
            return $orderItem->product->source_url;
        });

        Mail::to($currentUser)->send(new SendOrderedImages($reservedImages->toArray(),$currentUser));

        Cookie::queue('basket',null);

        return redirect()->route('home.products.all')->with('success' , 'خرید شما انجام و تصاویر برای شما ارسال شدند');
    }
}
