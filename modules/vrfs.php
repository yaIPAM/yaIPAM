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
		else if ($request->query->get('mode') == 'delete') {
			return $this->Page_Delete();
		}
		else if ($request->query->get('mode') == 'edit') {
			return $this->Page_Add(true);
		}
	}

	private function Page_Default() {
		global $tpl, $request;


		$range = \IPLib\Range\Subnet::fromString("::/0");

		echo $range->getAddressType()." - ".$range->getComparableStartString()." - ".$range->getComparableEndString();


		$tpl->assign("D_VRF_LIST", Model_VRF::getAll());
		$tpl->display("vrfs/vrf_index.html");
	}

	private function Page_Add(bool $edit = false) {
		global $request, $tpl;


		$VRF = new Model_VRF();

		if ($request->request->get('VRFID') && $VRF->getbyID($request->request->get('VRFID'))) {
			$edit = true;
			$tpl->assign(array(
				"D_MODE"    =>  "edit",
				"D_VRFName" =>  $VRF->getVRFName(),
				"D_VRFDescription"  =>  $VRF->getVRFDescription(),
				"D_VRFID"   =>  $VRF->getVRFID(),
				"D_VRFRT"   =>  $VRF->getVRFRT(),
				"D_VRFRD"   =>  $VRF->getVRFRD(),
			));
		}
		else {
			$edit = false;
			$tpl->assign("D_MODE", "add");
		}

		if ($request->request->get('submitForm1') != null) {

			if ($request->request->get('VRFName') == null) {
				MessageHandler::Warning("Bitte notwendige Felder ausfüllen", "Bitte wenigstens einen VRF Namen angeben.");
				$tpl->assign(array(
					"D_VRFName" =>  $request->request->get('VRFName'),
					"D_VRFDescription"  =>  $request->request->get('VRFDescription'),
					"D_VRFRT"   =>  $request->request->get('VRFRT'),
					"D_VRFRD"   =>  $request->request->get('VRFRD'),
				));
				$tpl->display("vrfs/vrf_add.html");

				return false;

			}

			$VRF->setVRFDescription($request->request->get('VRFDescription'));
			$VRF->setVRFName($request->request->get('VRFName'));
			$VRF->setVRFRD($request->request->get('VRFRD'));
			$VRF->setVRFRT($request->request->get('VRFRT'));

			if ($VRF->save() === false) {
				$tpl->assign(array(
					"D_VRFName" =>  $request->request->get('VRFName'),
					"D_VRFDescription"  =>  $request->request->get('VRFDescription'),
					"D_VRFRT"   =>  $request->request->get('VRFRT'),
					"D_VRFRD"   =>  $request->request->get('VRFRD'),
				));
				if (!$edit) {
					MessageHandler::Error("Anlagefehler", "Beim Anlegen der VRF ist ein Fehler aufgetreten.");
				}
				else {
					MessageHandler::Error("Bearbeitungsfehler", "Beim Bearbeiten der VRF ist ein Fehler aufgetreten.");
				}
			}
			else {
				if (!$edit) {
					MessageHandler::Success("VRF angelegt", "Die neue VRF wurde angelegt.");
				}
				else {
					MessageHandler::Success("VRF bearbeitet", "Die VRF wurde bearbeitet.");
				}
				return $this->Page_Default();
			}

		}


		$tpl->display("vrfs/vrf_add.html");
	}

	private function Page_Delete() {
		global $tpl, $request;

		$vrf = new Model_VRF();
		$vrfData = $vrf->getByID($request->request->getInt('VRFID'));

		if ($vrf->getVRFID() == 0) {
			MessageHandler::Warning("VRF existiert nicht", "Die ausgewählte VRF existiert nicht.");
			return $this->Page_Default();
		}

		if ($request->request->getBoolean('submitForm1')) {
			if ($vrf->delete()) {
				MessageHandler::Success("VRF gelöscht", "Die VRF wurde gelöscht.");
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
			}
		}


		$tpl->assign("D_VRF", $vrfData);
		$tpl->display("vrfs/vrf_delete.html");
	}
}

$Module = new Module_vrfs();