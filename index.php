<?php
use Symfony\Component\HttpFoundation\Session\Session;

require_once(__DIR__.'/bootstrap.php');
require_once SCRIPT_BASE.'/src/libs/MessageHandler.php';
require SCRIPT_BASE.'/src/libs/i18n.php';

$session = new Session();
$session->start();

// Very simple CSFR check
if (!$session->has('csfr')) {
    $session->set('csfr', uniqid('', true));
}

if (!$session->has('login')) {
    $session->set('login', false);
}

$dbase = new Framework\DBase($Config);
$dbal = $dbase->getDbal();
$EntityManager = $dbase->getEntityManager();

// Language setup

I18N::init('messages', SCRIPT_BASE.'/lang', 'en_US', array(
    '/^de((-|_).*?)?$/i' => 'de_DE',
    '/^en((-|_).*?)?$/i' => 'en_US',
    '/^es((-|_).*?)?$/i' => 'es_ES'
));

$tpl->assign("SITE_BASE", SITE_BASE);
$tpl->assign('D_SearchString', "");
$theme_url = rtrim(SITE_BASE, "/");
$tpl->assign("THEME_URL", $theme_url."/theme/default");
$tpl->assign("SITE_TITLE", $Config['general']['site_title']);


$app = new \Framework\Core($Config);
$app->handle($request, $whoops, $tpl, $session);
