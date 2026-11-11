<?php

return [
    'currency' => env('SEMAPON_CURRENCY', 'IDR'),
    'currency_symbol' => env('SEMAPON_CURRENCY_SYMBOL', 'Rp'),
    'trial_days' => (int) env('SEMAPON_TRIAL_DAYS', 14),
    'default_warranty_days' => (int) env('SEMAPON_DEFAULT_WARRANTY_DAYS', 30),

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 29000,
            'technicians_limit' => 1,
            'features' => [
                'order_limit' => 50,
                'technician_commission' => false,
                'export_reports' => false,
            ],
        ],
        'business' => [
            'name' => 'Business',
            'price' => 59000,
            'technicians_limit' => 3,
            'features' => [
                'order_limit' => null, // unlimited
                'technician_commission' => true,
                'export_reports' => true,
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 99000,
            'technicians_limit' => 10,
            'features' => [
                'order_limit' => null, // unlimited
                'technician_commission' => true,
                'export_reports' => true,
            ],
        ],
    ],
];
