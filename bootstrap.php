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

// Include default config
require 'config.dist.php';
// Defaults will be overwritten by a local configuration.
require 'config.php';

define("SITE_BASE", $general_config['sitebase']);

if (!isset($_SESSION['csfr'])) {
    $_SESSION['csfr'] = uniqid('', true);
}

if (!isset($_SESSION['login'])) {
    $_SESSION['login'] = false;
}



// Composer Autoloader
require SCRIPT_BASE . '/vendor/autoload.php';
require SCRIPT_BASE .'/src/libs/MessageHandler.php';
require SCRIPT_BASE .'/src/libs/IP.php';
require SCRIPT_BASE .'/src/libs/i18n.php';
require SCRIPT_BASE .'/src/libs/functions.php';

// Error Handling
$whoops = new \Whoops\Run;
$whoops->pushHandler(function () {
    ob_clean();
    exit;
});
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->writeToOutput(true);
$whoops->register();

// Language setup

I18N::init('messages', SCRIPT_BASE.'/lang', 'en_US', array(
    '/^de((-|_).*?)?$/i' => 'de_DE',
    '/^en((-|_).*?)?$/i' => 'en_US',
    '/^es((-|_).*?)?$/i' => 'es_ES'
));

// Basic DBAL Configuration

$dbal_config = new DBAL\Configuration();
$dbal = DriverManager::getConnection($dbase_config, $dbal_config);
$orm_config = Setup::createAnnotationMetadataConfiguration(array(SCRIPT_BASE.'/src/entities'), $general_config['devMode'], null, null, false);
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
$auditconfig->setUsernameCallable(function () {
    return $_SESSION['Username'];
});
$evm = new EventManager();
$auditManager = new AuditManager($auditconfig);
$auditManager->registerEvents($evm);
$EntityManager = EntityManager::create($dbase_config, $orm_config, $evm);
$EntityManager->getConfiguration()->addCustomStringFunction('inet_aton', 'Application\DQL\InetAtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('inet6_aton', 'Application\DQL\Inet6AtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('MATCH_AGAINST', 'Application\DQL\MatchAgainstFunction');


$a = array();

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
