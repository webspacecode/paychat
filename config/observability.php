<?php

return [
    'tenant_operational_logs_enabled' => env('TENANT_OPERATIONAL_LOGS_ENABLED', true),
    'tenant_operational_logs_max_read_lines' => env('TENANT_OPERATIONAL_LOGS_MAX_READ_LINES', 2000),
];
