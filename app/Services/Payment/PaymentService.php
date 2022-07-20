<?php
namespace App\Services\Payment;

use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Providers\IDPayProvider;


class PaymentService
{
    public const IDPAY = 'IDPayProvider';
    public const ZARINPAL = 'ZarinpalProvider';

    public function __construct(private string $providerName ,private RequestInterface $request)
    {

    }

    public function pay()
    {
        return $this->findProvider()->pay();
    }

    private function findProvider()
    {
         $className ='App\\Services\\Payment\\Providers\\' . $this->providerName;

         if(!class_exists($className))
         {
             throw new ProviderNotFoundException('درگاه پرداخت مورد نظر پیدا نشد');
         }

         return new $className($this->request);
    }
}
// $idPayRequest = new IDPayRequest([
//     'amount' =>100,
//     'user' =>$user,
// ]);

// $paymentService = new PaymentService(PaymentService::IDPAY , $idPayRequest);
// $paymentService->pay();
