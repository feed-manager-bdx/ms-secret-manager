<?php

namespace App\Http\Controllers;

use App\Helpers\GoogleSecretManagerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecretController extends Controller
{
    public function get(Request $request) {
        $helper = new GoogleSecretManagerHelper();

        return $helper->get('snapfeat-login-json');
    }
}
