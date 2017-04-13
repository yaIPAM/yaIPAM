<?php

require_once SCRIPT_BASE . '/models/Model_OTV.php';
require_once SCRIPT_BASE . '/models/Model_VLAN_Domain.php';

/**
 * otv.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.04.17
 * Time: 13:55
 */
class Module_OTV {

	public function __construct() {
		global $request;

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
		global $tpl;

		$tpl->assign("D_OTV_LIST", Model_OTV::getAll());

		$tpl->display("otv/otv_index.html");

	}

	private function Page_Add(bool $edit = false) {
		global $tpl, $request;

		$otv = new Model_OTV();
		//$otvDomains = new Model_OTV();

		$DomainsList = Model_VLAN_Domain::listDomains();

		if ($request->request->getInt('OTVID') != null) {
			$edit = (!empty($otv->selectByID($request->request->getInt('OTVID')))) ? true : false;
		}else {
			$edit = false;
		}

		$tpl->assign("D_MODE", ($edit) ? "edit" : "add");

		$domains = array();

		if ($edit) {

			$DomainsSelected = $otv->getOTVDomains();

			$tpl->assign(array(
				"D_OTV_ID" =>  $otv->getOTVID(),
				"D_OTV_NAME"   =>  $otv->getOTVName(),
				"D_OTV_DESCRIPTION" =>  $otv->getOTVDescription(),
			));
		} else {

			$DomainsSelected =  $request->request->get('OTVDomains');

			$tpl->assign(array(
				"D_OTV_ID" =>  $request->request->getInt('OTVID'),
				"D_OTV_NAME"   =>  $request->request->get('OTVName'),
				"D_OTV_DESCRIPTION" =>  $request->request->get('OTVDescription'),
			));
		}

		foreach ($DomainsList as $data) {
			$domains[]= array(
				"domain_id" =>  $data['domain_id'],
				"domain_name"   =>  $data['domain_name'],
				"domain_selected"   =>  (($DomainsSelected != null) && in_array($data['domain_id'], $DomainsSelected)) ? true : false,
			);
		}

		$tpl->assign("D_OTV_DOMAINS", $domains);

		if ($request->request->getBoolean('submitForm1') &&
			(empty ($request->request->get('OTVName')))) {

			MessageHandler::Warning("Leere Felder", "Bitte alle Felder ausfüllen.");
			return $tpl->display("otv/otv_add.html");
		}

		if ($request->request->getBoolean('submitForm1')) {
			$otv->setOTVName($request->request->get('OTVName'));
			$otv->setOTVDescription($request->request->get('OTVDescription'));
			$otv->setOTVDomains(($request->request->get('OTVDomains') != null) ? $request->request->get('OTVDomains') : array());

			if ($otv->save()) {
				MessageHandler::Success("OTV Instanz gespeichert", sprintf("Die OTV Instanz <strong>%s</strong> wurde gespeichert", $otv->getOTVName()));
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error("Fehler", "Die OTV Instanz konnte nicht gespeichert werden.");
			}
		}

		$tpl->display("otv/otv_add.html");
	}

	private function Page_Delete() {
		global $tpl, $request;


		$otv = new Model_OTV();
		$otvData = $otv->selectByID($request->request->getInt("OTVID"));

		if (empty($otvData)) {
			MessageHandler::Warning("OTV Instanz gibt es nicht", "Die OTV Instanz gibt es scheinbar nicht. Da kann ich nichts machen.");
			return $this->Page_Default();
		}

		if ($request->request->getBoolean('submitForm1')) {
			if ($otv->delete()) {
				MessageHandler::Success("OTV Instanz", "Die OTV Instanz wurde gelöscht.");
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
			}
		}


		$tpl->assign("D_OTV", $otvData[0]);
		$tpl->display("otv/otv_delete.html");
	}

}

$Module = new Module_OTV();