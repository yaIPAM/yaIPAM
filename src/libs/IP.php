<?php
/**
 * IP.php
 * Project: yaipam
 * User: ktammling
 * Date: 22.04.17
 * Time: 19:40
 */

define('STATE_ALLOCATED', 1);
define('STATE_RESERVED', 2);
define('STATE_EXPIRED', 3);

/**
 * @author phptuts
 * @copyright 2012
 * @link http://tutorialspots.com/
 */

function cidr2ip($cidr)
{
    $ip_arr = explode('/', $cidr);
    $start = $ip_arr[0];
    $nm = $ip_arr[1];
    $num = pow(2, 32 - $nm);
    $end = $start + $num - 1;
    return array($ip_arr[0], long2ip($end));
}

/*
 * Function by http://tutorialspots.com/php-convert-ip-range-to-cidr-353.html
 */
function ip2cidr($ips)
{
    $return = array();
    $num = ip2long($ips[1]) - ip2long($ips[0]) + 1;
    $bin = decbin($num);

    $chunk = str_split($bin);
    $chunk = array_reverse($chunk);
    $start = 0;

    while ($start < count($chunk)) {
        if ($chunk[$start] != 0) {
            $start_ip = isset($range) ? long2ip(ip2long($range[1]) + 1) : $ips[0];
            $range = cidr2ip($start_ip . '/' . (32 - $start));
            $return[] = $start_ip . '/' . (32 - $start);
        }
        $start++;
    }
    return $return;
}

function stateToText($ID)
{
    $states = array(
        1   =>  _('Allocated'),
        2   =>  _('Reserved'),
        3   =>  _('Expired'),
    );

    return $states[$ID];
}

function reverseNetmask(string $Netmask): string
{
    $Netmask = explode('.', $Netmask);

    return ($Netmask[0] ^ 255).".".($Netmask[1] ^ 255).".".($Netmask[2] ^ 255).".".($Netmask[3] ^ 255);
}

function ip2long6($ipv6)
{
    $ipv6long = "";
    $ip_n = inet_pton($ipv6);
    $bits = 15; // 16 x 8 bit = 128bit
    while ($bits >= 0) {
        $bin = sprintf("%08b", (ord($ip_n[$bits])));
        $ipv6long = $bin.$ipv6long;
        $bits--;
    }

    return gmp_strval(gmp_init($ipv6long, 2), 10);
}

function long2ip6($ipv6long)
{
    $ipv6 = "";
    $bin = gmp_strval(gmp_init($ipv6long, 10), 2);
    if (strlen($bin) < 128) {
        $pad = 128 - strlen($bin);
        for ($i = 1; $i <= $pad; $i++) {
            $bin = "0".$bin;
        }
    }
    $bits = 0;
    while ($bits <= 7) {
        $bin_part = substr($bin, ($bits*16), 16);
        $ipv6 .= dechex(bindec($bin_part)).":";
        $bits++;
    }
    // compress

    return inet_ntop(inet_pton(substr($ipv6, 0, -1)));
}
