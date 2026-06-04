<?php

$all = [
    'pos.access',
    'order.create',
    'order.edit',
    'order.cancel',
    'payment.collect',
    'discount.apply',
    'invoice.print',
    'kds.access',
    'kds.update',
    'table.manage',
    'product.manage',
    'report.view',
    'settings.view',
    'settings.manage',
    'password.change',
];

return [
    'permissions' => $all,

    'roles' => [
        'owner' => $all,

        'manager' => [
            'pos.access',
            'order.create',
            'order.edit',
            'order.cancel',
            'payment.collect',
            'discount.apply',
            'invoice.print',
            'kds.access',
            'kds.update',
            'table.manage',
            'product.manage',
            'report.view',
            'settings.view',
            'password.change',
        ],

        'cashier' => [
            'pos.access',
            'order.create',
            'order.edit',
            'payment.collect',
            'discount.apply',
            'invoice.print',
            'password.change',
        ],

        'kitchen' => [
            'kds.access',
            'kds.update',
            'password.change',
        ],

        'waiter' => [
            'pos.access',
            'order.create',
            'order.edit',
            'table.manage',
            'invoice.print',
            'password.change',
        ],

        'accountant' => [
            'invoice.print',
            'report.view',
            'settings.view',
            'password.change',
        ],
    ],
];
