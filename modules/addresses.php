<?php
require_once SCRIPT_BASE .'/models/Model_VRF.php';

/**
 * addresses.php
 * Project: yaipam
 * User: ktammling
 * Date: 23.04.17
 * Time: 11:13
 */
class Module_Addresses {

	public function __construct() {
		global $request;

		if ($request->query->get('mode') == null) {
			return $this->Page_Default();
		} else if ($request->query->get('mode') == "subnet") {
			return $this->Page_Subnet($request->query->get('id'));
		}
	}

	private function Page_Default() {
		global $tpl;


		$tpl->assign("D_VRF_LIST", Model_VRF::getWithRoot());
		$tpl->display("addresses/addresses_index.html");
	}

	private function Page_Subnet(int $ID = 0) {
		global $tpl;

		$tpl->display("addresses/addresses_subnet.html");

	}

}

$Module = new Module_Addresses();