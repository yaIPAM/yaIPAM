<?php
/**
 * Created by PhpStorm.
 * User: ktammling
 * Date: 16.06.17
 * Time: 15:32
 */

use Symfony\Component\HttpFoundation\Session\Session;

require_once(__DIR__.'/../bootstrap.php');

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