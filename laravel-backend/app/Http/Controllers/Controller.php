<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getWalletBalance(Request $request)
    {
        $address = $request->input('address');
        $infuraUrl = env('INFURA_URL');

        // ... lanjutkan request ke Infura
    }
}