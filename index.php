<?php
define("SITE_BASE", "/yaipam/");
define("SCRIPT_BASE", __DIR__);

// Composer Autoloader
require SCRIPT_BASE . '/vendor/autoload.php';
require SCRIPT_BASE .'/libs/MessageHandler.php';
require SCRIPT_BASE .'/config.php';

// Firing up Smarty as a template engine
$tpl = new Smarty();

$tpl->setTemplateDir(__DIR__.'/theme/default/html');
$tpl->setCompileDir(__DIR__.'/cache');
$tpl->setCacheDir(__DIR__.'/cache');
$tpl->setConfigDir(__DIR__.'/theme/default/configs');


$tpl->assign("SITE_BASE", SITE_BASE);
$tpl->assign("THEME_URL", SITE_BASE."/theme/default/");
$tpl->assign("SITE_TITLE", $general_config['site_title']);

// Basic DBAL Configuration

$dbal_config = new \Doctrine\DBAL\Configuration();
$dbal = \Doctrine\DBAL\DriverManager::getConnection($dbase_config, $dbal_config);
unset($dbal_config);
unset($dbase_config);

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

if (!isset($_GET['page']) or empty($_GET['page'])) {
	$module_file = "default";
}
else {
	$module_file = $_GET['page'];
	$tpl->assign("S_ACTIVE_MENU", $module_file);
}

$tpl->display("header.html");

if (file_exists(__DIR__."/modules/$module_file.php")) {
	require_once __DIR__."/modules/$module_file.php";
}
else {
	$tpl->display("errors/404.html");
}

$tpl->display("footer.html");