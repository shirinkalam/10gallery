<?php

namespace App\Sevices\Payment\Providers;

use App\Services\Payment\Contracts\PaybleInterface;
use App\Services\Payment\Contracts\VerifaibleInterface;
use App\Services\Payment\Contracts\RequestInterface;

class ZarinpalProvider implements PaybleInterface,VerifaibleInterface
{


    public function pay()
    {

    }

    public function verify()
    {

    }
}
