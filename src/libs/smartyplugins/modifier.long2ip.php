<?php
/**
 * modifier.long2ip.php
 * Project: yaipam
 * User: ktammling
 * Date: 30.04.17
 * Time: 11:45
 */

function smarty_modifier_long2ip($ip): string {

    if (is_resource($ip)) {
        $ip = stream_get_contents($ip);
    }
    $IP = \IP::create($ip);

	return $IP;

}