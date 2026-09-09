<?php

// 1. FORCE THE STORAGE ENVIRONMENT REDIRECT IMMEDIATELY
$_ENV['LOG_CHANNEL'] = 'stderr';
$_ENV['APP_STORAGE'] = '/tmp/storage';

// 2. Clear out any lingering default server parameters
if (!file_exists('/tmp/storage/logs')) {
    @mkdir('/tmp/storage/logs', 0777, true);
}

// Keep your existing laravel loader lines below intact:
require __DIR__ . '/../public/index.php';
