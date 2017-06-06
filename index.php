<?php
require_once(__DIR__.'/bootstrap.php');
require_once SCRIPT_BASE.'/src/libs/MessageHandler.php';

$siteBase = rtrim($Config['general']['sitebase'], "/");
define("SITE_BASE", $siteBase);

// Language setup

I18N::init('messages', SCRIPT_BASE.'/lang', 'en_US', array(
    '/^de((-|_).*?)?$/i' => 'de_DE',
    '/^en((-|_).*?)?$/i' => 'en_US',
    '/^es((-|_).*?)?$/i' => 'es_ES'
));


error_reporting(E_ALL ^ E_NOTICE);

// Firing up Smarty as a template engine
$tpl = new Smarty();

$tpl->setTemplateDir(__DIR__.'/theme/default/html');
$tpl->setCompileDir(__DIR__.'/cache');
$tpl->setCacheDir(__DIR__.'/cache');
$tpl->setConfigDir(__DIR__.'/theme/default/configs');
$tpl->setPluginsDir(__DIR__.'/src/libs/smartyplugins');


$tpl->assign("SITE_BASE", SITE_BASE);
$tpl->assign("THEME_URL", SITE_BASE."/theme/default/");
$tpl->assign("SITE_TITLE", $Config['general']['site_title']);


$app = new \Framework\Core();
$app->handle($request, $whoops, $tpl);
