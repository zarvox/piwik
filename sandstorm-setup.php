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
// Adapted from the interesting pieces of plugins/Installation/Controller.php
use Piwik\Application\Environment;
use Piwik\Access;
use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Updater;
use Piwik\Version;

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
DbHelper::createAnonymousUser();
print("Created DB tables\n");
flush();

PluginManager::getInstance()->loadActivatedPlugins();
PluginManager::getInstance()->loadPluginTranslations();

// Apply updates/migrations
Access::getInstance();
$updatesPerformed = Access::doAsSuperUser(function () {
	$updater = new Updater();
	$componentsWithUpdateFile = $updater->getComponentUpdates();

	if (empty($componentsWithUpdateFile)) {
		return false;
	}
	$result = $updater->updateComponents($componentsWithUpdateFile);
	print_r($result);
	return $result;
});
print("updates performed:\n");
print_r($updatesPerformed);
print("\n");
flush();
Updater::recordComponentSuccessfullyUpdated('core', Version::VERSION);

// Enable geoip_pecl geolocation backend.
LocationProvider::setCurrentProvider("geoip_pecl");

// Create a default site.
$siteIdsCount = Access::doAsSuperUser(function () {
	return count(APISitesManager::getInstance()->getAllSitesId());
});
if ($siteIdsCount <= 0) {
	$name = "Website";
	$url = "http://example.com";
	$ecommerce = 0;
	$result = Access::doAsSuperUser(function () use ($name, $url, $ecommerce) {
		return APISitesManager::getInstance()->addSite($name, $url, $ecommerce);
	});
}
print("created site, enabling IP forwarding\n");
#$config = Config::getInstance();
#$config->General['proxy_client_headers'] = ['HTTP_X_REAL_IP'];
#$config->forceSave();
#print("enabled IP forwarding\n");
flush();
