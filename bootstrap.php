<?php
/**
 * bootstrap.php
 * Project: yaipam
 * User: ktammling
 * Date: 23.04.17
 * Time: 10:52
 */

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL;
use SimpleThings\EntityAudit\AuditConfiguration;
use SimpleThings\EntityAudit\AuditManager;
use Doctrine\Common\EventManager;

define("SCRIPT_BASE", __DIR__);

session_start();

/*
 * Loading default config and local config.
 * Local config will overwrite defaults.
 */
$ConfigDefault = require 'config.dist.php';
$Config = require 'config.php';
$Config = array_merge($ConfigDefault, $Config);


// Very simple CSFR check
if (!isset($_SESSION['csfr'])) {
    $_SESSION['csfr'] = uniqid('', true);
}

if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = false;
}

// Composer Autoloader
require SCRIPT_BASE.'/vendor/autoload.php';
require SCRIPT_BASE.'/src/libs/MessageHandler.php';
require SCRIPT_BASE.'/src/libs/IP.php';
require SCRIPT_BASE.'/src/libs/i18n.php';
require SCRIPT_BASE.'/src/libs/functions.php';

// Error Handling
$whoops = new \Whoops\Run;
$whoops->pushHandler(function() {
    ob_clean();
    exit;
});
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->writeToOutput(true);
$whoops->register();

// Basic DBAL Configuration

$dbal_config = new DBAL\Configuration();
$dbal = DriverManager::getConnection($Config['dbase'], $dbal_config);
$orm_config = Setup::createAnnotationMetadataConfiguration(array(SCRIPT_BASE.'/src/entities'), $Config['general']['devMode'], null, null, false);
if (extension_loaded('apcu')) {
    $orm_config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
    $orm_config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
    $orm_config->setResultCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
}
$auditconfig = new AuditConfiguration();
$auditconfig->setAuditedEntityClasses(array(
    'Entity\Addresses',
    'Entity\Prefixes',
    'Entity\User',
    'Entity\Vrfs'
));
$auditconfig->setGlobalIgnoreColumns(array(
    'created_at',
    'updated_at'
));
$auditconfig->setUsernameCallable(function() {
    return $_SESSION['Username'];
});
$evm = new EventManager();
$auditManager = new AuditManager($auditconfig);
$auditManager->registerEvents($evm);
$EntityManager = EntityManager::create($Config['dbase'], $orm_config, $evm);
$EntityManager->getConfiguration()->addCustomStringFunction('inet_aton', 'Application\DQL\InetAtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('inet6_aton', 'Application\DQL\Inet6AtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('MATCH_AGAINST', 'Application\DQL\MatchAgainstFunction');

// Request wrapper from Symfony.
use Symfony\Component\HttpFoundation\Request;

$request = new Request(
    $_GET,
    $_POST,
    array(),
    $_COOKIE,
    $_FILES,
    $_SERVER
);
