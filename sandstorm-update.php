<?php

if (!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__) == '/' ? '' : dirname(__FILE__));
}
if (file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

if (!defined('PIWIK_PRINT_ERROR_BACKTRACE')) {
    define('PIWIK_PRINT_ERROR_BACKTRACE', true);
}

use Piwik\Application\Environment;
use Piwik\Config;

$environment = new Environment(null);
$environment->init();

// Apply idempotent updates
$config = Config::getInstance();
$config->General['proxy_client_headers'] = ['HTTP_X_REAL_IP'];
$config->forceSave();
print("enabled IP forwarding\n");
flush();

