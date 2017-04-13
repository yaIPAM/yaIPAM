<?php

require_once SCRIPT_BASE . '/models/Model_VLAN_Domain.php';
require_once SCRIPT_BASE . '/models/Model_VLAN.php';

/**
 * vlandomains.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.04.17
 * Time: 11:53
 */
class Module_VlanDomains {

	/**
	 * Module_VlanDomains constructor.
	 */
	public function __construct() {
		global $request;

		if ($request->query->get('mode') == null){
			return $this->Page_Default();
		} else if ($request->query->get('mode') == "add") {
			return $this->Page_Add(false);
		} else if ($request->query->get('mode') == "edit") {
			return $this->Page_Add(true);
		} else if ($request->query->get('mode') == "delete") {
			return $this->Page_Delete();
		}

	}

	/**
	 *
	 */
	private function Page_Default() {
		global $tpl;

		$tpl->assign("D_VLAN_DOMAINS", Model_VLAN_Domain::listDomains());
		$tpl->display("vlandomains/domain_index.html");
	}

	/**
	 * @param bool $edit
	 */
	private function Page_Add(bool $edit = false) {
		global $tpl, $request, $dbal;

		$VlanDomain = new Model_VLAN_Domain();

		if ($request->request->getInt('ID') != null) {
			$edit = (!empty($VlanDomain->selectByID($request->request->getInt('ID')))) ? true : false;
		}else {
			$edit = false;
		}

		$tpl->assign("D_MODE", ($edit) ? "edit" : "add");

		if ($edit) {
			$tpl->assign(array(
				"D_DOMAIN_ID" =>  $VlanDomain->getDomainID(),
				"D_DOMAIN_NAME"   =>  $VlanDomain->getDomainName(),
				"D_DOMAIN_DESCRIPTION" =>  $VlanDomain->getDomainDescription(),
			));
		} else {
			$tpl->assign(array(
				"D_DOMAIN_ID" =>  $request->request->getInt('ID'),
				"D_DOMAIN_NAME"   =>  $request->request->get('DomainName'),
				"D_DOMAIN_DESCRIPTION" =>  $request->request->get('DomainDescription'),
			));
		}

		if ($request->request->getBoolean('submitForm1') &&
			(empty ($request->request->get('DomainName')) or
			empty($request->request->get('DomainDescription')))) {

			MessageHandler::Warning("Leere Felder", "Bitte alle Felder ausfüllen.");
			return $tpl->display("vlandomains/domain_add.html");
		}

		if ($request->request->getBoolean('submitForm1')) {
			$VlanDomain->setDomainName($request->request->get('DomainName'));
			$VlanDomain->setDomainDescription($request->request->get('DomainDescription'));

			if ($VlanDomain->save()) {
				MessageHandler::Success("Domain gespeichert", sprintf("Die Domain <strong>%s</strong> wurde gespeichert", $VlanDomain->getDomainName()));
				return $this->Page_Default();
			}
			else {
				MessageHandler::Error("Fehler", "Die Domain konnte nicht gespeichert werden.");
			}
		}


		$tpl->display("vlandomains/domain_add.html");
	}

	private function Page_Delete() {
		global $tpl, $request, $dbal;


		$dbal->beginTransaction();
		$VlanDomain = new Model_VLAN_Domain();
		$DomainData = $VlanDomain->selectByID($request->request->getInt("ID"));

		if (empty($DomainData)) {
			MessageHandler::Warning("VLAN Domain existiert nicht", "Die Vlan Domain existiert nicht.");
			return $this->Page_Default();
		}

		if ($request->request->getBoolean('submitForm1')) {
			if ($VlanDomain->delete() && Model_VLAN::DeleteAllByDomain($VlanDomain->getDomainID())) {
				MessageHandler::Success("Domain gelöscht", "Die Domain und alle dazugehörigen VLANs wurde gelöscht.");
				$dbal->commit();
				return $this->Page_Default();
			}
			else {
				$dbal->rollBack();
				MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
			}
		}

		$tpl->assign("D_VLAN_COUNT", Model_VLAN::CountAllByDomain($VlanDomain->getDomainID()));
		$tpl->assign("D_DOMAIN", $DomainData);
		$tpl->display("vlandomains/domain_delete.html");



	}

}

$Module = new Module_VlanDomains();