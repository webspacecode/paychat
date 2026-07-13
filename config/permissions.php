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
    'bakery.manage',
    'password.change',
    'tenant.modules.manage',
    'registration.access',
    'registration.settings.manage',
    'registration.programs.view',
    'registration.programs.create',
    'registration.programs.update',
    'registration.programs.archive',
    'registration.participants.view',
    'registration.participants.create',
    'registration.participants.update',
    'registration.registrations.view',
    'registration.registrations.create',
    'registration.registrations.update',
    'registration.registrations.cancel',
    'registration.registrations.renew',
    'registration.payments.view',
    'registration.payments.collect',
    'registration.payments.refund',
    'registration.reports.view',
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
            'bakery.manage',
            'password.change',
        ],

        'cashier' => [
            'pos.access',
            'order.create',
            'order.edit',
            'payment.collect',
            'discount.apply',
            'invoice.print',
            'bakery.manage',
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
