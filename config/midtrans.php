<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'snap_url_sandbox' => env('MIDTRANS_SNAP_URL_SANDBOX', 'https://app.sandbox.midtrans.com/snap/v1/transactions'),
    'snap_url_production' => env('MIDTRANS_SNAP_URL_PRODUCTION', 'https://app.midtrans.com/snap/v1/transactions'),
    'snap_js_url_sandbox' => env('MIDTRANS_SNAP_JS_URL_SANDBOX', 'https://app.sandbox.midtrans.com/snap/snap.js'),
    'snap_js_url_production' => env('MIDTRANS_SNAP_JS_URL_PRODUCTION', 'https://app.midtrans.com/snap/snap.js'),
];