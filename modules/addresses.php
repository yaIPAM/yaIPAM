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

	private $CurrentSubnet;
	private $CurrentVRF;

	public function __construct() {
		global $request;

		if ($request->query->get('id') != null) {
			$this->setCurrentSubnet($request->query->get('id'));
		}

		if ($request->query->get('mode') == null) {
			return $this->Page_Default();
		} else if ($request->query->get('mode') == "subnet") {
			return $this->Page_Subnet();
		} else if ($request->query->get('mode') == "subnetadd") {
			return $this->Page_SubnetAdd();
		}
	}

	private function Page_Default() {
		global $tpl;


		$tpl->assign("D_VRF_LIST", Model_VRF::getWithRoot());
		$tpl->display("addresses/addresses_index.html");
	}

	private function Page_SubnetAdd(bool $edit = false) {
		global $tpl, $request;

		$SubnetID = $this->getCurrentSubnet();

		$CurrentSubnet = new Model_Subnet();
		$CurrentSubnet->getByID($SubnetID);

		$VRF = Model_VRF::getAllExcept($CurrentSubnet->getMasterVRF());

		$tpl->assign(array(
			"D_PrefixID"    =>  $SubnetID,
			"D_VRFS"    =>  $VRF,
		));

		if ($request->request->getBoolean('Submit')) {
			if ($request->request->get('PrefixName') == null) {
				MessageHandler::Error(_('Empty field'), _('Please fill in all required fields'));

				$tpl->assign(array(
					"D_PrefixName"  =>  $request->request->get('PrefixName'),
					"D_PrefixState" =>  $request->request->get('PrefixState'),
					"D_PrefixDescription"   =>  $request->request->get('PrefixDescription'),
				));

				return $tpl->display("subnets/subnet_add.html");
			}

			if (!(\IPLib\Range\Subnet::fromString($request->request->get('PrefixName')))) {
				MessageHandler::Error(_('Invalid Prefix'), _('This Prefix is invalid.'));

				$tpl->assign(array(
					"D_PrefixName"  =>  $request->request->get('PrefixName'),
					"D_PrefixState" =>  $request->request->get('PrefixState'),
					"D_PrefixDescription"   =>  $request->request->get('PrefixDescription'),
				));

				return $tpl->display("subnets/subnet_add.html");
			}

			$ParentID = Model_Subnet::CalculateParentID($request->request->get('PrefixName'), $CurrentSubnet->getMasterVRF());

			$PrefixName = $request->request->get('PrefixName');
			$Prefix = \IPLib\Range\Subnet::fromString($PrefixName);
			$PrefixName = explode("/", $PrefixName);

			$NewSubnet = new Model_Subnet();
			$NewSubnet->setPrefix($PrefixName[0]);
			$NewSubnet->setParentID($ParentID);
			$NewSubnet->setMasterVRF($CurrentSubnet->getMasterVRF());
			$NewSubnet->setPrefixDescription($request->request->get('PrefixDescription'));
			$NewSubnet->setRangeFrom($Prefix->getComparableStartString());
			$NewSubnet->setRangeTo($Prefix->getComparableEndString());
			$NewSubnet->setPrefixLength($PrefixName[1]);
			$NewSubnet->setAFI($Prefix->getAddressType());
			$NewSubnet->setPrefixState($request->request->get('PrefixState'));

			if ($NewSubnet->save()) {
				MessageHandler::Success(_('Prefix saved'), _('The prefix has been saved'));

				return $this->Page_Subnet($NewSubnet->getPrefixID());
			}
			else {
				MessageHandler::Error(_('Prefix not saved'), _('Error saving the prefix'));

				$tpl->assign(array(
					"D_PrefixName"  =>  $request->request->get('PrefixName'),
					"D_PrefixState" =>  $request->request->get('PrefixState'),
					"D_PrefixDescription"   =>  $request->request->get('PrefixDescription'),
				));

				return $tpl->display("subnets/subnet_add.html");
			}
		}


		$tpl->display("subnets/subnet_add.html");
	}

	private function Page_Subnet(int $SubnetID = 0) {
		global $tpl;

		if ($SubnetID != 0) {
			$ID = $SubnetID;
		}
		else {
			$ID = $this->getCurrentSubnet();
		}

		$Subnet = new Model_Subnet();
		$Subnet->getByID($ID);

		$VRF = new Model_VRF();
		$VRF->getByID($Subnet->getMasterVRF());

		$tpl->assign(array(
			"D_PrefixID"    =>  $Subnet->getPrefixID(),
			"D_MasterVRF"   =>  $Subnet->getMasterVRF(),
			"D_MasterVRF_Name"  =>  $VRF->getVRFName(),
			"D_AFI" =>  $Subnet->getAFI(),
			"D_Prefix"  =>  $Subnet->getPrefix(),
			"D_RangeTo" =>  $Subnet->getRangeTo(),
			"D_RangeFrom"   =>  $Subnet->getRangeFrom(),
			"D_PrefixDescription"   =>  $Subnet->getPrefixDescription(),
			"D_PrefixLength"    =>  $Subnet->getPrefixLength(),
			"D_ParentID"    =>  $Subnet->getParentID(),
			"D_Subnets" =>  Model_Subnet::getSubPrefixes($Subnet->getPrefixID()),
		));



		$tpl->display("addresses/addresses_subnet.html");

	}

	/**
	 * @return mixed
	 */
	public function getCurrentSubnet() {
		return $this->CurrentSubnet;
	}

	/**
	 * @param mixed $CurrentSubnet
	 */
	public function setCurrentSubnet($CurrentSubnet) {
		$this->CurrentSubnet = $CurrentSubnet;
	}

	/**
	 * @return mixed
	 */
	public function getCurrentVRF() {
		return $this->CurrentVRF;
	}

	/**
	 * @param mixed $CurrentVRF
	 */
	public function setCurrentVRF($CurrentVRF) {
		$this->CurrentVRF = $CurrentVRF;
	}



}

$Module = new Module_Addresses();