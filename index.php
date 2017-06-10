<?php
require_once(__DIR__.'/bootstrap.php');
require_once SCRIPT_BASE.'/src/libs/MessageHandler.php';

if ($Config['general']['devMode']) {
    error_reporting(E_ALL);
}

$siteBase = rtrim($Config['general']['sitebase'], "/");
if (empty($siteBase)) {
    $siteBase = "/";
}
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https")) {
    $siteProto = "https";
}
else {
    $siteProto = "http";
}
$siteBase = sprintf("%s://%s%s", $siteProto, $_SERVER['SERVER_NAME'], $siteBase);
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
$theme_url = rtrim(SITE_BASE, "/");
$tpl->assign("THEME_URL", $theme_url."/theme/default");
$tpl->assign("SITE_TITLE", $Config['general']['site_title']);


$app = new \Framework\Core();
$app->handle($request, $whoops, $tpl);
