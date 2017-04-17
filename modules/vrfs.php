<?php

require_once SCRIPT_BASE . '/models/Model_VRF.php';

/**
 * vrfs.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 12:17
 */
class Module_vrfs {

	public function __construct() {
		global $request;

		if ($request->query->get('mode') == null) {
			return $this->Page_Default();
		}
		else if ($request->query->get('mode') == "add") {
			return $this->Page_Add(false);
		}
	}

	private function Page_Default() {
		global $tpl;


		$range = \IPLib\Range\Subnet::fromString("::/0");

		echo $range->getAddressType()." - ".$range->getComparableStartString()." - ".$range->getComparableEndString();


		$tpl->assign("D_VRF_LIST", Model_VRF::getAll());
		$tpl->display("vrfs/vrf_index.html");
	}

	private function Page_Add(bool $edit = false) {
		global $request, $tpl;



		$tpl->display("vrfs/vrf_add.html");
	}
}

$Module = new Module_vrfs();