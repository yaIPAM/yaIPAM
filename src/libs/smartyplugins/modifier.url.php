<?php
/**
 * links.php
 * Project: yaipam
 * User: ktammling
 * Date: 29.04.17
 * Time: 11:12
 */

function smarty_modifier_url(string $link, bool $internal = true): string
{
    $link = ltrim($link, "/");
    $link = ($internal)  ? SITE_BASE."/$link" : "$link";

    return $link;
}
