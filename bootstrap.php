<?php
/**
 * bootstrap.php
 * Project: yaipam
 * User: ktammling
 * Date: 23.04.17
 * Time: 10:52
 */

use Symfony\Component\HttpFoundation\Request;


define("SCRIPT_BASE", __DIR__);



/*
 * Loading default config and local config.
 * Local config will overwrite defaults.
 */
$ConfigDefault = require 'config.dist.php';
$Config = require 'config.php';
$Config = array_merge($ConfigDefault, $Config);


// Composer Autoloader
require SCRIPT_BASE.'/vendor/autoload.php';
require SCRIPT_BASE.'/src/libs/MessageHandler.php';
require SCRIPT_BASE.'/src/libs/IP.php';
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

$request = new Request(
    $_GET,
    $_POST,
    array(),
    $_COOKIE,
    $_FILES,
    $_SERVER
);

// Firing up Smarty as a template engine
$tpl = new Smarty();

$tpl->setTemplateDir(__DIR__.'/theme/default/html');
$tpl->setCompileDir(__DIR__.'/cache');
$tpl->setCacheDir(__DIR__.'/cache');
$tpl->setConfigDir(__DIR__.'/theme/default/configs');
$tpl->setPluginsDir(__DIR__.'/src/libs/smartyplugins');

if ($Config['general']['devMode']) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 1);
}
else {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set('display_errors', 0);
}

if (defined("UNIT_TEST") && UNIT_TEST)
{
    $siteBase = "/";
} else {
    $siteBase = rtrim($Config['general']['sitebase'], "/");
    if (empty($siteBase)) {
        $siteBase = "/";
    }
    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
        $siteProto = "https";
    } else {
        $siteProto = "http";
    }
    $siteBase = sprintf("%s://%s%s", $siteProto, $_SERVER['SERVER_NAME'], $siteBase);
}

define("SITE_BASE", $siteBase);