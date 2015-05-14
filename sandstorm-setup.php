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

// Actually do something useful
use Piwik\Application\Environment;
use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;

$environment = new Environment(null);
$environment->init();

$dbInfos = array(
	'host'          => '127.0.0.1',
	'username'      => 'root',
	'password'      => '',
	'dbname'        => 'piwik',
	'tables_prefix' => '',
	'adapter'       => 'PDO\MYSQL',
	'port'          => 3306,
	'schema'        => Config::getInstance()->database['schema'],
	'type'          => Config::getInstance()->database['type']
);
Db::createDatabaseObject($dbInfos);
DbHelper::createDatabase("piwik");
DbHelper::createTables("piwik");
// Create a default site.

// Create a default user?  Nah, I don't think this is needed.

