<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NfcController extends Controller
{
    /**
     * Show the NFC reader page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('nfc.reader');
    }
}
