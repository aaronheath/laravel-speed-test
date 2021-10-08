<?php

return [
    'enabled' => env('BANDWIDTH_CHECK_ENABLED', true),
    'report_url' => env('BANDWIDTH_CHECK_REPORT_URL', 'https://example.com/api/bandwidth-check'),
];