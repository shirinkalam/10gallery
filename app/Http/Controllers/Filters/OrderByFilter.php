<?php

namespace App\Http\Controllers\Filters;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderByFilter extends Controller
{
    public function newest()
    {
        return Product::orderBy('created_at','desc')->get();
    }
}
