<?php

return [
    'registration_management' => [
        'name' => 'Registration & Membership',
        'available' => true,
        'default_enabled' => false,
        'entitlement_key' => null,
        'access_permission' => 'registration.access',
        'rollout' => 'stable',
        'settings' => [
            'participant_label' => ['type' => 'string', 'default' => 'Participant', 'max' => 50],
            'program_label' => ['type' => 'string', 'default' => 'Program', 'max' => 50],
            'batch_label' => ['type' => 'string', 'default' => 'Batch', 'max' => 50],
            'instructor_label' => ['type' => 'string', 'default' => 'Instructor', 'max' => 50],
            'registration_label' => ['type' => 'string', 'default' => 'Registration', 'max' => 50],
            'allow_partial_payment' => ['type' => 'boolean', 'default' => true],
            'allow_waitlist' => ['type' => 'boolean', 'default' => false],
            'allow_loyalty' => ['type' => 'boolean', 'default' => false],
            'allow_manual_fee_adjustment' => ['type' => 'boolean', 'default' => false],
            'require_initial_payment' => ['type' => 'boolean', 'default' => false],
            'default_payment_policy' => ['type' => 'string', 'default' => 'standard', 'max' => 50],
            'default_side_effect_policy' => ['type' => 'string', 'default' => 'registration_default', 'max' => 50],
        ],
    ],
];
