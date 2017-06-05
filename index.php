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

if (empty($request->query->get('url'))) {
    $url = "default";
} else {
    $url = $request->query->get('url');
}

if ($_SESSION['login'] == false) {
    $url = "login/";
    $tpl->assign("S_LOGIN", false);
} else {
    $tpl->assign("S_LOGIN", true);
}

$urlArray = array();
$urlArray = explode("/", $url);

$controller = $urlArray[0];
array_shift($urlArray);
if (empty($urlArray[0])) {
    $action = "IndexAction";
} else {
    $action = $urlArray[0].'Action';
}
array_shift($urlArray);
$queryString = $urlArray;

$namespace = '\Controller\\';
$controllerName = $controller;
$controller = ucwords($controller);
$model = rtrim($controller, 's');
$controller .= 'Controller';
$controller = $namespace . $controller;
if (method_exists($controller, $action)) {
    try {
        $dispatch = new $controller($controllerName, $action);
        call_user_func_array(array($dispatch, $action), $queryString);
    } catch (Exception $e) {
        $whoops->handleException($e);
    }
} else {
    $dispatch = new \Controller\ErrorController('\Controller\ErrorController', 'NotfoundAction');
    call_user_func_array(array($dispatch, 'NotFoundAction'), $queryString);
}
