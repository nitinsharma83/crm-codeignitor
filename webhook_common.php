<?php
/**
 * webhook_common.php  (place in the project root, next to db_config.php)
 * Shared helpers for webhook handlers: safe logging + JSON responses.
 */

// Logs go to /logs_private (blocked from the web by its own .htaccess).
if (!defined('WH_LOG_DIR'))     define('WH_LOG_DIR', __DIR__ . '/logs_private');
if (!defined('WH_LOG_MAX_BYTES')) define('WH_LOG_MAX_BYTES', 5 * 1024 * 1024); // rotate at 5 MB

/** Append a line to a private log file; rotates to <name>.1 when it grows past 5 MB. */
function wh_log($name, $message) {
    if (!is_dir(WH_LOG_DIR)) { @mkdir(WH_LOG_DIR, 0750, true); }
    $file = WH_LOG_DIR . '/' . basename($name);
    clearstatcache(true, $file);
    if (is_file($file) && filesize($file) > WH_LOG_MAX_BYTES) {
        @rename($file, $file . '.1');          // keeps only one backup => logs can't grow forever
    }
    @file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/** Send a JSON response with an HTTP status code and stop. */
function wh_respond($httpCode, array $payload) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}
