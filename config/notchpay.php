<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NotchPay Configuration
    |--------------------------------------------------------------------------
    | Renseignez vos clés depuis votre tableau de bord NotchPay.
    | https://business.notchpay.co
    */

    'public_key'   => env('NOTCHPAY_PUBLIC_KEY', ''),
    'private_key'  => env('NOTCHPAY_PRIVATE_KEY', ''),
    'base_url'     => env('NOTCHPAY_BASE_URL', 'https://api.notchpay.co'),
    'callback_url' => env('NOTCHPAY_CALLBACK_URL', ''),

    /*
    | Mode test : active les logs détaillés et accepte tout paiement
    */
    'test_mode'    => env('NOTCHPAY_TEST_MODE', true),

];
