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
define("SCRIPT_BASE", __DIR__);

session_start();

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
$whoops->pushHandler(function() {
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
#$orm_config = Setup::createYAMLMetadataConfiguration(array(SCRIPT_BASE.'/config/yml'), $general_config['devMode'], null, null, false);
$EntityManager = EntityManager::create($dbase_config, $orm_config);
$EntityManager->getConfiguration()->addCustomStringFunction('inet_aton', 'Application\DQL\InetAtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('inet6_aton', 'Application\DQL\Inet6AtonFunction');
$EntityManager->getConfiguration()->addCustomStringFunction('MATCH_AGAINST', 'Application\DQL\MatchAgainstFunction');



// Now we are ready
use Symfony\Component\HttpFoundation\Request;
$request = new Request(
    $_GET,
    $_POST,
    array(),
    $_COOKIE,
    $_FILES,
    $_SERVER
);