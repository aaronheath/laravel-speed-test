<?php

return [
    'enabled' => env('BANDWIDTH_CHECK_ENABLED', true),
    'report_url' => env('BANDWIDTH_CHECK_REPORT_URL', 'https://example.com/api/bandwidth-check'),
    'download_url' => env('BANDWIDTH_CHECK_DOWNLOAD_URL', 'https://example.com/50MB.file'),
    'download_filesize' => env('BANDWIDTH_CHECK_FILESIZE', 50), // MB
];