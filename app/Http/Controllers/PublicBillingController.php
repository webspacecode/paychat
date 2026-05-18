<?php

namespace App\Http\Controllers;

class PublicBillingController extends Controller
{
    public function token(string $uuid)
    {
        return view('billing.token', compact('uuid'));
    }

    public function invoice(string $uuid)
    {
        return view('billing.invoice', compact('uuid'));
    }
}
