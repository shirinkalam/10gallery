<?php
namespace App\Services\Payment;
use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Providers\IDPayProvider;
use App\Services\Payment\Requests\IDPayRequest;

class PaymentService
{
    public const IDPAY = 'IDPayProvider';
    public const ZARINPAL = 'ZarinpalProvider';

    public function __construct(private string $providerName,
                                private RequestInterface $request)
    {

    }

    public function pay()
    {
        return $this->findProvider()->pay();
    }

    private function findProvider()
    {
        $className ='App\\Sevices\\Payment\\Providers\\' . $this->providerName;

        if(!class_exists($className)){
            throw new ProviderNotFoundException('درگاه پرداخت انتخاب شده یافت نشد');
        }

        return new $className($this->request);
    }
}

$idPayRequest = new IDPayRequest([
    'amount'=> 1000,
    'user'=> $user,
]);

$idPayRequest  =new PaymentService(PaymentService::IDPAY , $idPayRequest);
$paymentService->pay();
