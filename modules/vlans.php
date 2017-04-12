<?php

require_once SCRIPT_BASE.'/models/Model_VLAN.php';
require_once SCRIPT_BASE . '/models/Model_VLAN_Domain.php';

/**
 * vlans.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 12:03
 */
class Module_vlans {

	private $VlanDomainSelected = 0;

	public function __construct() {
		global $tpl, $request;

		if (empty($request->query->get('mode'))) {
			return $this->Page_Default();
		}
		else if ($request->query->get('mode') == "add") {
			return $this->Page_Add(false);
		}
		else if ($request->query->get('mode') == "edit") {
			return $this->Page_Add(true);
		} else if ($request->query->get('mode') == "delete") {
			return $this->Page_Delete();
		}
	}

	private function Page_Default() {
		global $tpl, $request, $vlans_config;

		$VlanDomains = new Model_VLAN_Domain();
		$Vlans = new Model_VLAN();

		if (empty($request->request->getInt('VlanDomain')) && $this->getVlanDomainSelected() == 0) {
			$this->setVlanDomainSelected($VlanDomains->selectFirst()['domain_id']);
			$tpl->assign("D_VLAN_DOMAIN", $this->getVlanDomainSelected());
		}
		else if (!empty($request->request->getInt('VlanDomain')) && $this->getVlanDomainSelected() == 0) {
			$this->setVlanDomainSelected($request->request->getInt('VlanDomain'));
			$tpl->assign("D_VLAN_DOMAIN", $request->request->getInt('VlanDomain'));
		}

		$vlans_list = $Vlans->getAllByDomain($this->getVlanDomainSelected());

		/*
		 * Calculating some free VLANs. Maybe someone has a better idea how to do this.
		 */

		$firstVlan = $Vlans->firstVLANByDomain($this->getVlanDomainSelected());
		$lastVlan = $Vlans->LastVLANByDomain($this->getVlanDomainSelected());

		if ($firstVlan > 1) {
			if (( $firstVlan['VlanID'] - 1) == 1) {
				$vlan_list[0] = array(
					"VlanID"    =>  1,
					"VlanName"  =>  "<i>Frei</i>",
					"VlanFree"  =>  true,
					"FirstFree" =>  1,
				);
			}
			else {
				$vlan_list[0] = array(
					"VlanID"    =>  "1-".($firstVlan['VlanID']-1),
					"VlanName"  =>  "<i>Frei</i>",
					"VlanFree"  =>  true,
					"FirstFree" =>  1,
				);
			}

		}

		$n = $firstVlan['VlanID'];
		$FirstFree = 0;

		foreach ($vlans_list as $data) {

			if ($n < $data['VlanID'] && ($n+1) != $data['VlanID']) {
				if (($n + 1) == ($data['VlanID'] - 1)) {
					$VlanID = $n + 1;
				} else {
					$VlanID = ($n + 1) . "-" . ($data['VlanID'] - 1);
				}
				$FirstFree = ($n + 1);

				$vlan_list[] = array(
					"VlanID" => $VlanID,
					"VlanName" => "<i>Frei</i>",
					"VlanFree" => true,
					"FirstFree" =>  $FirstFree,
				);
			}

			$vlan_list[] = array(
				"VlanID"    =>  $data['VlanID'],
				"VlanName"  =>  $data['VlanName'],
				"ID"    =>  $data['ID'],

			);
			$n = $data['VlanID'];
		}

		if ($lastVlan['VlanID'] < $vlans_config['maxID']) {
			if (( $vlans_config['maxID'] - $lastVlan['VlanID']) == 1) {
				$vlan_list[] = array(
					"VlanID"    =>  $vlans_config['maxID'],
					"VlanName"  =>  "<i>Frei</i>",
					"VlanFree"  =>  true,
					"FirstFree" =>  $vlans_config['maxID'],
				);
			}
			else {
				$vlan_list[] = array(
					"VlanID"    =>  ($lastVlan['VlanID']+1)."-".$vlans_config['maxID'],
					"VlanName"  =>  "<i>Frei</i>",
					"VlanFree"  =>  true,
					"FirstFree" =>  ($lastVlan['VlanID']+1),
				);
			}
		}

		/*
		 * The End of calculation.
		 */


		$tpl->assign("D_VLANS_LIST", $vlan_list);
		$tpl->assign("D_VLAN_DOMAIN_LIST", Model_VLAN_Domain::listDomains());
		$tpl->display("vlans/vlan_index.html");

	}

	private function Page_Delete() {
		global $dbal, $tpl, $request;


		$vlan = new Model_VLAN();
		$vlanData = $vlan->get($request->request->getInt("ID"));

		if ($request->request->get('VlanDomain') != null) {
			$this->setVlanDomainSelected($request->request->get('VlanDomain'));
		}

		if (!$vlan) {
			MessageHandler::Warning("VLAN gibt es nicht", "Das VLAN existiert scheinbar nicht. Da kann ich nichts machen.");
			return $this->Page_Default();
		}

		if ($request->request->getBoolean('submitForm1')) {
			if ($vlan->delete()) {
				MessageHandler::Success("VLAN gelöscht", "Das VLAN wurde gelöscht.");
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
			}
		}


		$tpl->assign("D_VLAN", $vlanData);
		$tpl->display("vlans/vlan_delete.html");



	}


	/**
	 * @param bool $edit
	 */
	private function Page_add(bool $edit) {
		global $tpl, $request;

		$tpl->assign("D_VLAN_DOMAIN_LIST", Model_VLAN_Domain::listDomains());
		$vlan = new Model_VLAN();

		$edit = ($vlan->get($request->request->getInt('ID'))) ? true : false;
		$tpl->assign("D_MODE", ($edit) ? "edit" : "add");

		if ($edit) {
			$tpl->assign(array(
				"D_VLAN_ID" =>  $vlan->getVlanID(),
				"D_VLAN_NAME"   =>  $vlan->getVlanName(),
				"D_VLAN_DOMAIN" =>  $vlan->getVlanDomainID(),
				"D_ID"  =>  $vlan->getID(),
			));
		} else {
			$tpl->assign(array(
				"D_VLAN_ID" =>  $request->request->getInt('VlanID'),
				"D_VLAN_NAME"   =>  $request->request->get('VlanName'),
				"D_VLAN_DOMAIN" =>  $request->request->getInt('VlanDomain'),
			));
		}



		if ($request->request->getBoolean('submitForm1')) {
			$FindVlan = new Model_VLAN();
			$FindVlan = $FindVlan->findByVLANID($request->request->getInt('VlanID'), $request->request->getInt('VlanDomain'));
			if (!$FindVlan or $FindVlan['ID'] == $request->request->getInt('ID')) {
				$vlan->setVlanDomainID($request->request->getInt('VlanDomain'));
				$vlan->setVlanName($request->request->get('VlanName'));
				$vlan->setVlanID($request->request->getInt('VlanID'));
				if (!$edit && $vlan->create()) {
					MessageHandler::Success("VLAN eintragen",sprintf("Das VLAN %s (%s) wurde eingetragen.", $vlan->getVlanName(), $vlan->getVlanID()));
					return $this->Page_Default();
				}
				else if ($edit && $vlan->save()) {
					MessageHandler::Success("VLAN bearbeitet",sprintf("Das VLAN %s (%s) wurde angepasst.", $vlan->getVlanName(), $vlan->getVlanID()));
					return $this->Page_Default();
				}
				else {
					MessageHandler::Error("Fehler", "Beim Eintragen gab es einen merkwürdigen Fehler.");
				}
			}
			else {
				MessageHandler::Warning("VLAN ID bereits vergeben", "Das VLAN ist bereits vergeben. Versuche ein anderes.");
				$tpl->assign(array(
					"D_VLAN_ID" =>  $request->request->getInt('VlanID'),
					"D_VLAN_NAME"   =>  $request->request->get('VlanName'),
					"D_VLAN_DOMAIN" =>  $request->request->getInt('VlanDomain'),
				));
			}

		}

		$tpl->display("vlans/vlan_add.html");
	}

	/**
	 * @return int
	 */
	public function getVlanDomainSelected(): int {
		return $this->VlanDomainSelected;
	}

	/**
	 * @param int $VlanDomainSelected
	 */
	public function setVlanDomainSelected(int $VlanDomainSelected) {
		$this->VlanDomainSelected = $VlanDomainSelected;
	}

}

$Module = new Module_vlans();