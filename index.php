<?php

// Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

// Firing up Smarty as a template engine
$tpl = new Smarty();

$tpl->setTemplateDir(__DIR__.'/theme/default/html');
$tpl->setCompileDir(__DIR__.'/cache');
$tpl->setCacheDir(__DIR__.'/cache');
$tpl->setConfigDir(__DIR__.'/theme/default/configs');