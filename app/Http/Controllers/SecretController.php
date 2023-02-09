<?php

namespace App\Http\Controllers;

use App\Helpers\GoogleSecretManagerHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecretController extends Controller
{
    public function get(Request $request) {
        $helper = new GoogleSecretManagerHelper();
        $secret = $request->query('secret');

        return $helper->get($secret);
    }

    public function post(Request $request) {
        $helper = new GoogleSecretManagerHelper();
        $name = $request->name;
        $payload = $request->payload;
        $labels = $request->labels;
        $annotations = $request->annotations;

        return response($helper->write($name, $payload, $labels, $annotations));
    }

    public function all(Request $request) {
        $helper = new GoogleSecretManagerHelper();

        return response($helper->getAll($request->query('filter')));
    }


}
