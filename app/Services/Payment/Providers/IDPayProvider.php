<?php

namespace App\Sevices\Payment\Providers;

use App\Services\Payment\Contracts\PaybleInterface;
use App\Services\Payment\Contracts\VerifaibleInterface;
use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Contracts\AbstractProviderInterface;


class IDPayProvider extends AbstractProviderInterface implements PaybleInterface,VerifaibleInterface
{


    public function pay()
    {

    }

    public function verify()
    {

    }
}
