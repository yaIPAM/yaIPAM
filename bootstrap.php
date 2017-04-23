<?php
/**
 * bootstrap.php
 * Project: yaipam
 * User: ktammling
 * Date: 23.04.17
 * Time: 10:52
 */

define("SITE_BASE", "/yaipam/");
define("SCRIPT_BASE", __DIR__);

// Composer Autoloader
require SCRIPT_BASE . '/vendor/autoload.php';
require SCRIPT_BASE .'/libs/MessageHandler.php';
require SCRIPT_BASE .'/libs/IP.php';
require SCRIPT_BASE .'/config.php';

// Basic DBAL Configuration

$dbal_config = new \Doctrine\DBAL\Configuration();
$dbal = \Doctrine\DBAL\DriverManager::getConnection($dbase_config, $dbal_config);
$orm_config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(SCRIPT_BASE.'/entities/'), $general_config['devMode']);
$EntityManager = \Doctrine\ORM\EntityManager::create($dbase_config, $orm_config);
unset($dbal_config);
unset($dbase_config);
unset($orm_config);

// Error Handling
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

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