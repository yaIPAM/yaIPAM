<?php
/**
 * modifier.long2ip6.php
 * Project: yaipam
 * User: ktammling
 * Date: 30.04.17
 * Time: 11:46
 */

function smarty_modifier_long2ip6(string $ip): string
{
    return long2ip6($ip);
}
