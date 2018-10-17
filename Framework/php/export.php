<?php

/**
 * Get current year value
 * 
 * A full numeric representation of a year, 4 digits, by default
 * 
 * @param boolean $short A two digit representation of a year,
 *      example as: 99 or 03
 * 
 * @example 1999 or 2003
 * 
 * @return string
*/
function year($short = false) {
	return date("Y");
}

/**
 * Get current month value
 * 
 * Numeric representation of a month, with leading zeros, by default.
 * 
 * @param boolean $textual A short textual representation of a month, 
 *      three letters, example as: Jan through Dec.
 * 
 * @example 01 through 12
 * 
 * @return string
*/
function month($textual = false) {
	if ($textual) {
		return date("M");
	} else {
		return date("m");
	}
}

/**
 * 这个函数只适用于
 * 
 * @return string[] 返回本地服务器的IP列表
*/
function localhost() {
	$print  = shell_exec("ifconfig | grep \"inet \"");
	$print  = StringHelpers::LineTokens($print);
	$iplist = [];
	
	# inet 172.17.0.1  netmask 255.255.0.0  broadcast 0.0.0.0
	# inet 192.168.1.237  netmask 255.255.255.0  broadcast 192.168.1.255
	# inet 127.0.0.1  netmask 255.0.0.0
	foreach($print as $line) {
		$line     = explode(" ", trim($line));
		$iplist[] = $line[1];
	}

	return $iplist;
}