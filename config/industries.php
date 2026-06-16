<?php

return [
    'default' => 'general',

    'aliases' => [
        'restaurant' => 'cafe',
        'cafe' => 'cafe',
        'bakery' => 'bakery',
        'salon' => 'salon',
        'service' => 'salon',
        'services' => 'salon',
        'retail' => 'retail',
        'general' => 'general',
        'other' => 'general',
    ],

    'features' => [
        'cafe' => [
            'pos' => true,
            'dine_in' => true,
            'kds' => true,
            'token_management' => true,
            'inventory' => true,
            'appointments' => false,
            'staff_assignment' => true,
            'gst_invoice' => true,
            'customer_management' => true,
            'reports' => true,
        ],

        'bakery' => [
            'pos' => true,
            'dine_in' => true,
            'kds' => true,
            'token_management' => true,
            'inventory' => true,
            'appointments' => false,
            'staff_assignment' => true,
            'gst_invoice' => true,
            'customer_management' => true,
            'reports' => true,
            'bakery_management' => true,
        ],

        'salon' => [
            'pos' => true,
            'dine_in' => false,
            'kds' => false,
            'token_management' => false,
            'inventory' => true,
            'appointments' => true,
            'staff_assignment' => true,
            'gst_invoice' => true,
            'customer_management' => true,
            'reports' => true,
        ],

        'retail' => [
            'pos' => true,
            'dine_in' => false,
            'kds' => false,
            'token_management' => false,
            'inventory' => true,
            'appointments' => false,
            'staff_assignment' => false,
            'gst_invoice' => true,
            'customer_management' => true,
            'reports' => true,
        ],

        'general' => [
            'pos' => true,
            'dine_in' => false,
            'kds' => false,
            'token_management' => false,
            'inventory' => true,
            'appointments' => false,
            'staff_assignment' => false,
            'gst_invoice' => true,
            'customer_management' => true,
            'reports' => true,
        ],
    ],
];
