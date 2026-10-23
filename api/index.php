<?php

/**
 * Vercel Serverless Function entry point for PolicyFlow Laravel Application
 */

// Ensure writable directories in /tmp for serverless Vercel environment
$storagePath = '/tmp/storage';
foreach (['framework/views', 'framework/cache/data', 'framework/sessions', 'logs'] as $dir) {
    $fullDir = $storagePath . '/' . $dir;
    if (!is_dir($fullDir)) {
        @mkdir($fullDir, 0755, true);
    }
}

// Copy sqlite database to /tmp if not present
$sqliteOrigin = __DIR__ . '/../database/database.sqlite';
$sqliteTmp = '/tmp/database.sqlite';
if (!file_exists($sqliteTmp)) {
    if (file_exists($sqliteOrigin)) {
        @copy($sqliteOrigin, $sqliteTmp);
    } else {
        @touch($sqliteTmp);
    }
}

// Forward execution to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
