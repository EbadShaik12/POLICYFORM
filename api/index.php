<?php

/**
 * Vercel Serverless Function entry point for PolicyFlow Laravel Application
 */

// 1. Setup writable directories in /tmp for serverless Vercel environment
$storagePath = '/tmp/storage';
$cachePath = $storagePath . '/bootstrap/cache';

$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $cachePath,
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 2. Set environment variables for storage, compiled views, and framework cache paths
$envVars = [
    'APP_STORAGE' => $storagePath,
    'VIEW_COMPILED_PATH' => $storagePath . '/framework/views',
    'APP_CONFIG_CACHE' => $cachePath . '/config.php',
    'APP_EVENTS_CACHE' => $cachePath . '/events.php',
    'APP_PACKAGES_CACHE' => $cachePath . '/packages.php',
    'APP_ROUTES_CACHE' => $cachePath . '/routes-v7.php',
    'APP_SERVICES_CACHE' => $cachePath . '/services.php',
];

foreach ($envVars as $key => $val) {
    putenv("{$key}={$val}");
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
}

// 3. Fallback encryption key if missing on Vercel deployment
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY']) && empty($_SERVER['APP_KEY'])) {
    $fallbackKey = 'base64:VBlI8rSTKkEnSE33fCmr51bP7ZD5kawIvEMRvfatdks=';
    putenv("APP_KEY={$fallbackKey}");
    $_ENV['APP_KEY'] = $fallbackKey;
    $_SERVER['APP_KEY'] = $fallbackKey;
}

// 4. Pre-seed package manifest into /tmp, filtering out any dev-only classes
$originCache = __DIR__ . '/../bootstrap/cache';
if (file_exists($originCache . '/packages.php') && !file_exists($cachePath . '/packages.php')) {
    $packages = require $originCache . '/packages.php';
    if (is_array($packages)) {
        foreach ($packages as $pkgName => $pkgData) {
            if (!empty($pkgData['providers'])) {
                $packages[$pkgName]['providers'] = array_values(array_filter(
                    $pkgData['providers'],
                    fn ($cls) => class_exists($cls)
                ));
                if (empty($packages[$pkgName]['providers'])) {
                    unset($packages[$pkgName]);
                }
            }
        }
        @file_put_contents($cachePath . '/packages.php', '<?php return ' . var_export($packages, true) . ';');
    }
}

// 5. Copy sqlite database to /tmp if not present
$sqliteOrigin = __DIR__ . '/../database/database.sqlite';
$sqliteTmp = '/tmp/database.sqlite';
if (!file_exists($sqliteTmp)) {
    if (file_exists($sqliteOrigin) && filesize($sqliteOrigin) > 0) {
        @copy($sqliteOrigin, $sqliteTmp);
    } else {
        @touch($sqliteTmp);
    }
}

// 6. Forward execution to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
