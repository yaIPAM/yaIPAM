<?php
/**
 * modifier.long2subnet.php
 * Project: yaipam
 * User: ktammling
 * Date: 30.04.17
 * Time: 11:45
 */

function smarty_modifier_long2subnet($subnet, $prefixlength): string
{
    if (is_resource($subnet)) {
        $subnet = stream_get_contents($subnet);
    }
    $IP = \IPBlock::create($subnet, $prefixlength);

    return $IP;
}
