<?php
require_once(__DIR__.'/bootstrap.php');
require_once SCRIPT_BASE.'/src/libs/MessageHandler.php';

$tpl->assign("SITE_BASE", SITE_BASE);
$theme_url = rtrim(SITE_BASE, "/");
$tpl->assign("THEME_URL", $theme_url."/theme/default");
$tpl->assign("SITE_TITLE", $Config['general']['site_title']);


$app = new \Framework\Core();
$app->handle($request, $whoops, $tpl);
