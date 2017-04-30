<?php
/**
 * links.php
 * Project: yaipam
 * User: ktammling
 * Date: 29.04.17
 * Time: 11:12
 */

function smarty_modifier_createlink(string $link, string $title, string $class = "", bool $internal = true): string {

	$class = (!empty($class)) ? "class=\"$class\"" : "";
	$link = ($internal)  ? SITE_BASE."/$link" : "$link";

	$string = "<a href=\"$link\" $class>$title</a>";

	return $string;

}