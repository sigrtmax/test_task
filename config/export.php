<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Export URL
    |--------------------------------------------------------------------------
    |
    | The URL of the external system to which orders will be exported
    | when their status changes to "confirmed".
    |
    */
    'url' => env('EXPORT_URL', 'https://httpbin.org/post'),
];
