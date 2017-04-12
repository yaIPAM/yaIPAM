<?php

class Module_Default {
	public function __construct() {
		global $tpl;

		$tpl->display("default.html");
	}
}

$Module = new Module_Default();