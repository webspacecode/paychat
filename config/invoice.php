<?php

return [

'industries' => [

'restaurant' => [
    'templates' => [
        '80mm' => 'invoices.restaurant.80mm',
        '58mm' => 'invoices.restaurant.thermal_58mm',
        'token' => 'invoices.restaurant.token'
    ],
    'features' => [
        'table' => true,
        'kot' => true
    ]
],

'cafe' => [
    'templates' => [
        '80mm' => 'invoices.cafe.80mm',
        '58mm' => 'invoices.cafe.thermal_58mm',
        'token' => 'invoices.cafe.token'
    ],
    'features' => [
        'table' => true,
        'kot' => true
    ]
],

'retail' => [
    'templates' => [
        'a4' => 'invoices.retail.a4',
        '80mm' => 'invoices.retail.80mm'
    ],
    'features' => [
        'customer' => true,
        'tax_breakup' => true
    ]
],

'healthcare' => [
    'templates' => [
        'a4' => 'invoices.healthcare.a4'
    ],
    'features' => [
        'patient' => true,
        'doctor' => true
    ]
],

'services' => [
    'templates' => [
        'a4' => 'invoices.services.a4'
    ],
    'features' => [
        'description' => true
    ]
]

]

];