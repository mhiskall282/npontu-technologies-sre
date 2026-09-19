<?php

declare(strict_types=1);

/**
 * Vercel Serverless Function Bridge for Laravel 11/12.
 *
 * Because Vercel serverless functions operate in a read-only filesystem with
 * write permissions strictly confined to `/tmp`, this bootstrap file prepares
 * ephemeral storage directories for compiled views, sessions, logs, and caches
 * before handing execution to Laravel's front controller.
 */

// 1. Prepare writable directory tree in /tmp
$ephemeralDirectories = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/views',
];

foreach ($ephemeralDirectories as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Set runtime environment pointers to writable /tmp storage
putenv('VIEW_COMPILED_PATH=/tmp/views');
putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_EVENTS_CACHE=/tmp/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');

// 3. Delegate request execution to Laravel front controller
require __DIR__.'/../public/index.php';
