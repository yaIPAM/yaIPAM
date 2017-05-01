<?php
/**
 * IP.php
 * Project: yaipam
 * User: ktammling
 * Date: 22.04.17
 * Time: 19:40
 */

function reverseNetmask(string $Netmask): string {
	$Netmask = explode('.', $Netmask);

	return ($Netmask[0] ^ 255).".".($Netmask[1] ^ 255).".".($Netmask[2] ^ 255).".".($Netmask[3] ^ 255);
}

function ip2long6($ipv6) {
	$ipv6long = "";
	$ip_n = inet_pton($ipv6);
	$bits = 15; // 16 x 8 bit = 128bit
	while ($bits >= 0) {
		$bin = sprintf("%08b",(ord($ip_n[$bits])));
		$ipv6long = $bin.$ipv6long;
		$bits--;
	}

	return gmp_strval(gmp_init($ipv6long,2),10);
}

function long2ip6($ipv6long) {
	$ipv6 = "";
	$bin = gmp_strval(gmp_init($ipv6long,10),2);
	if (strlen($bin) < 128) {
		$pad = 128 - strlen($bin);
		for ($i = 1; $i <= $pad; $i++) {
			$bin = "0".$bin;
		}
	}
	$bits = 0;
	while ($bits <= 7) {
		$bin_part = substr($bin,($bits*16),16);
		$ipv6 .= dechex(bindec($bin_part)).":";
		$bits++;
	}
	// compress

	return inet_ntop(inet_pton(substr($ipv6,0,-1)));
}