<?php
namespace App\Http\Controllers\Filters;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class PriceFilter extends Controller
{
    public function value(Request $r)
    {
            $price_range = explode('to', $r);
            dd($price_range);
    }
}
