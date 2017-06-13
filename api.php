<?php
/**
 * api.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.06.17
 * Time: 17:47
 */

require_once(__DIR__.'/bootstrap.php');
$app = new Framework\API();
$app->handle($request, $whoops, $tpl);