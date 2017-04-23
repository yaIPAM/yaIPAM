<?php
require_once 'bootstrap.php';

// Firing up Smarty as a template engine
$tpl = new Smarty();

$tpl->setTemplateDir(__DIR__.'/theme/default/html');
$tpl->setCompileDir(__DIR__.'/cache');
$tpl->setCacheDir(__DIR__.'/cache');
$tpl->setConfigDir(__DIR__.'/theme/default/configs');


$tpl->assign("SITE_BASE", SITE_BASE);
$tpl->assign("THEME_URL", SITE_BASE."/theme/default/");
$tpl->assign("SITE_TITLE", $general_config['site_title']);

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