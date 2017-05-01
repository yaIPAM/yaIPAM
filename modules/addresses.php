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
		} else if ($request->query->get('mode') == 'subnetedit') {
			return $this->Page_SubnetAdd(true);
		} else if ($request->query->get('mode') == "subnetdelete") {
			return $this->Page_SubnetDelete();
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

		if (!empty($CurrentSubnet) && $edit) {
			$tpl->assign(array(
				"D_MODE"    =>  "edit",
				"D_PrefixID"    =>  $CurrentSubnet->getPrefixID(),
				"D_PrefixName"  =>  $CurrentSubnet->getPrefix()."/".$CurrentSubnet->getPrefixLength(),
				"D_PrefixState" =>  $CurrentSubnet->getPrefixState(),
				"D_PrefixDescription"   =>  $CurrentSubnet->getPrefixDescription(),
			));
		}

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
					"D_PrefixState" =>  $request->request->getInt('PrefixState'),
				));

				return $tpl->display("subnets/subnet_add.html");
			}

			if (!(\IPLib\Range\Subnet::fromString($request->request->get('PrefixName')))) {
				MessageHandler::Error(_('Invalid Prefix'), _('This Prefix is invalid.'));

				$tpl->assign(array(
					"D_PrefixName"  =>  $request->request->get('PrefixName'),
					"D_PrefixState" =>  $request->request->get('PrefixState'),
					"D_PrefixDescription"   =>  $request->request->get('PrefixDescription'),
					"D_PrefixState" =>  $request->request->getInt('PrefixState'),
				));

				return $tpl->display("subnets/subnet_add.html");
			}

			if (!$edit) {
				$ParentID = Model_Subnet::CalculateParentID($request->request->get('PrefixName'), $CurrentSubnet->getMasterVRF());
			}

			$PrefixName = $request->request->get('PrefixName');
			$Prefix = \IPLib\Range\Subnet::fromString($PrefixName);
			$PrefixName = explode("/", \IPLib\Range\Subnet::fromString($PrefixName)->toString());

			if (!$edit) {
				$NewSubnet = new Model_Subnet();
				$NewSubnet->setParentID($ParentID);
			}
			else {
				$NewSubnet = $CurrentSubnet;
			}

			$NewSubnet->setPrefix($PrefixName[0]);
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


	private function Page_SubnetDelete() {
		global $tpl, $request;


		$CurrentSubnet = new Model_Subnet();
		$CurrentSubnet->getByID($this->getCurrentSubnet());

		if (empty($CurrentSubnet)) {
			MessageHandler::Warning(_('Prefix not existing'), _('The selected prefix does not exist.'));
			return $this->Page_Subnet();
		}

		if ($request->request->getBoolean('submitForm1') && $request->request->getInt('DeleteOption') == 1) {
			if ($CurrentSubnet->delete(1)) {
				MessageHandler::Success(_('Prefix deleted'), _('The prefix and all nested prefixes/addresses have been deleted.'));
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error(_("Ooops!"), _('Something unexpected went wrong!'));
			}
		} else if ($request->request->getBoolean('submitForm1') && $request->request->getInt('DeleteOption') == 2) {
			if ($CurrentSubnet->delete(2)) {
				MessageHandler::Success(_('Prefix deleted'), _('The prefix has been deleted'));
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error(_("Ooops!"), _('Something unexpected went wrong!'));
			}
		}


		$tpl->assign(array(
			"D_Prefix"  =>  $CurrentSubnet->getPrefix(),
			"D_PrefixLength"    =>  $CurrentSubnet->getPrefixLength(),
			"D_PrefixID"    =>  $CurrentSubnet->getPrefixID(),
		));
		$tpl->display("subnets/subnet_delete.html");
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

		$IPData = IPBlock::create($Subnet->getPrefix()."/".$Subnet->getPrefixLength());

		$tpl->assign(array(
			"D_PrefixID"    =>  $Subnet->getPrefixID(),
			"D_MasterVRF"   =>  $Subnet->getMasterVRF(),
			"D_MasterVRF_Name"  =>  $VRF->getVRFName(),
			"D_AFI" =>  $Subnet->getAFI(),
			"D_Prefix"  =>  $Subnet->getPrefix(),
			"D_RangeTo" =>  $IPData->getLastIp(),
			"D_RangeFrom"   =>  $IPData->getFirstIp(),
			"D_PrefixDescription"   =>  $Subnet->getPrefixDescription(),
			"D_PrefixLength"    =>  $Subnet->getPrefixLength(),
			"D_ParentID"    =>  $Subnet->getParentID(),
			"D_Subnets" =>  Model_Subnet::getSubPrefixes($Subnet->getPrefixID()),
			"D_Breadcrumbs"  =>  Model_Subnet::createSubnetBreadcrumbs($ID),
			"D_NetworkMask" =>  $IPData->getMask(),
			"D_Broadcast_Address" => $IPData->getFirstIp(),
			"D_NetworkNumber_Addresses" =>  $IPData->getNbAddresses(),
			"D_Network_Wildcard"    =>  reverseNetmask($IPData->getMask()),
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