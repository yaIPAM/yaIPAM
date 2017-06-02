<?php
namespace Controller;

require_once SCRIPT_BASE . '/models/Model_VLAN.php';
require_once SCRIPT_BASE . '/models/Model_VLAN_Domain.php';
require_once SCRIPT_BASE . '/models/Model_OTV.php';

/**
 * VlansController.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 12:03
 */
class VlansController extends BaseController {

    private $edit = false;

	private $VlanDomainSelected = 0;

	public function IndexAction() {
		global $vlans_config;

        $this->CheckAccess(\Service\User::GROUP_USER);

		$VlanDomains = new \Model_VLAN_Domain();
		$Vlans = new \Model_VLAN();

		$this->req->request->getInt('VlanDomain');
		if ($this->req->request->getInt('VlanDomain') == null && $this->getVlanDomainSelected() == 0) {
			$this->setVlanDomainSelected((int)$VlanDomains->selectFirst()['domain_id']);
			$this->set("D_VLAN_DOMAIN", $this->getVlanDomainSelected());
		}
		else if ($this->req->request->getInt('VlanDomain') != null && $this->getVlanDomainSelected() == 0) {
			$this->setVlanDomainSelected($this->req->request->getInt('VlanDomain'));
			$this->set("D_VLAN_DOMAIN", $this->req->request->getInt('VlanDomain'));
		}

		$vlans_list = $Vlans->getAllByDomain($this->getVlanDomainSelected());

		/*
		 * Calculating some free VLANs. Maybe someone has a better idea how to do this.
		 */

		$firstVlan = $Vlans->firstVLANByDomain($this->getVlanDomainSelected());
		$lastVlan = $Vlans->LastVLANByDomain($this->getVlanDomainSelected());

		if ($firstVlan['VlanID'] > 1) {
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
				"OTVVlan"   =>  $data['OTVVlan'],
				"Overlay"    =>  $data['Overlay'],
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


		$this->set("D_VLANS_LIST", $vlan_list);
		$this->set("D_VLAN_DOMAIN_LIST", \Model_VLAN_Domain::listDomains());

        return $this->view();
	}

	public function DeleteAction() {

        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

		$vlan = new \Model_VLAN();
		$vlanData = $vlan->get($this->req->request->getInt("ID"));

		if ($this->req->request->get('VlanDomain') != null) {
			$this->setVlanDomainSelected($this->req->request->get('VlanDomain'));
		}

		if (!$vlan) {
			\MessageHandler::Warning("VLAN gibt es nicht", "Das VLAN existiert scheinbar nicht. Da kann ich nichts machen.");
			$this->_tplfile = 'vlans/index.html';
			return $this->IndexAction();
		}

		if ($this->req->request->getBoolean('submitForm1')) {
			if ($vlan->delete()) {
				\MessageHandler::Success("VLAN gelöscht", "Das VLAN wurde gelöscht.");
                $this->_tplfile = 'vlans/index.html';
                return $this->IndexAction();
			}
			else {
				\MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
			}
		}

		$this->set("D_VLAN", $vlanData);

        return $this->view();
	}

	public function EditAction() {

        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

	    $this->edit = true;
	    $this->_tplfile = 'vlans/add.html';
	    return $this->AddAction();
    }

	/**
	 * @param bool $edit
	 */
	public function AddAction() {
		global $vlans_config;

        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

		$this->set("D_VLAN_DOMAIN_LIST", \Model_VLAN_Domain::listDomains());
		$vlan = new \Model_VLAN();

		if ($this->req->request->get('VlanDomain') != null) {
			$this->setVlanDomainSelected($this->req->request->get('VlanDomain'));
			$this->set("D_VLAN_DOMAIN_BACK", $this->getVlanDomainSelected());
		}

		$this->edit = ($vlan->get($this->req->request->getInt('ID'))) ? true : false;
		$this->set("D_MODE", ($this->edit) ? "edit" : "add");
		$this->set("D_OTV_LIST", \Model_OTV::getAll());

		if ($this->edit) {
			$this->set(array(
				"D_VLAN_ID" =>  $vlan->getVlanID(),
				"D_VLAN_NAME"   =>  $vlan->getVlanName(),
				"D_VLAN_DOMAIN" =>  $vlan->getVlanDomainID(),
				"D_ID"  =>  $vlan->getID(),
				"D_VLAN_OTVDOMAIN"  =>  $vlan->getVlanOTVDomain(),
			));
		} else {
			$this->set(array(
				"D_VLAN_ID" =>  $this->req->request->getInt('VlanID'),
				"D_VLAN_NAME"   =>  $this->req->request->get('VlanName'),
				"D_VLAN_DOMAIN" =>  $this->req->request->getInt('VlanDomain'),
				"D_VLAN_OTVDOMAIN"  =>  $this->req->request->getInt('VlanOTVDomain')
			));
		}

		if ($this->req->request->getBoolean('submitForm1') &&
			(empty($this->req->request->getInt('VlanID')) or
			empty ($this->req->request->get('VlanName')) or
			empty($this->req->request->getInt('VlanDomain')))) {

			\MessageHandler::Warning("Leere Felder", "Bitte alle Felder ausfüllen.");
            return $this->view();
		}

		if ($this->req->request->getBoolean('submitForm1') && ($this->req->request->getInt('VlanID') < 1 or $this->req->request->getInt('VlanID') > $vlans_config['maxID'])) {
			\MessageHandler::Warning("Out of range", sprintf("Die VLAN ID muss zwischen 1 und %s liegen", $vlans_config['maxID']));
            return $this->view();
		}


		if ($this->req->request->getBoolean('submitForm1')) {
			$FindVlan = new \Model_VLAN();
			$FindVlan = $FindVlan->findByVLANID($this->req->request->getInt('VlanID'), $this->req->request->getInt('VlanDomain'));
			if (!$FindVlan or $FindVlan['ID'] == $this->req->request->getInt('ID')) {
				$vlan->setVlanDomainID($this->req->request->getInt('VlanDomain'));
				$vlan->setVlanName($this->req->request->get('VlanName'));
				$vlan->setVlanID($this->req->request->getInt('VlanID'));
				$vlan->setVlanOTVDomain($this->req->request->getInt('VlanOTVDomain'));
				if (!$this->edit && $vlan->create()) {
					\MessageHandler::Success("VLAN eintragen",sprintf("Das VLAN %s (%s) wurde eingetragen.", $vlan->getVlanName(), $vlan->getVlanID()));
					$this->_tplfile = 'vlans/index.html';
					return $this->IndexAction();
				}
				else if ($this->edit && $vlan->save()) {
					\MessageHandler::Success("VLAN bearbeitet",sprintf("Das VLAN %s (%s) wurde angepasst.", $vlan->getVlanName(), $vlan->getVlanID()));
                    $this->_tplfile = 'vlans/index.html';
                    return $this->IndexAction();
				}
				else {
					\MessageHandler::Error("Fehler", "Beim Eintragen gab es einen merkwürdigen Fehler.");
				}
			}
			else {
				\MessageHandler::Warning("VLAN ID bereits vergeben", "Das VLAN ist bereits vergeben. Versuche ein anderes.");
				$this->set(array(
					"D_VLAN_ID" =>  $this->req->request->getInt('VlanID'),
					"D_VLAN_NAME"   =>  $this->req->request->get('VlanName'),
					"D_VLAN_DOMAIN" =>  $this->req->request->getInt('VlanDomain'),
					"D_VLAN_OTVDOMAIN"  =>  $this->req->request->getInt('VlanOTVDomain'),
				));
			}

		}

        return $this->view();
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