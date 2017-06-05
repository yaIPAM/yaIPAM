<?php

namespace Controller;

/**
 * OtvController.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.04.17
 * Time: 13:55
 */
class OtvController extends BaseController
{
    private $edit = false;

    public function IndexAction()
    {
        $this->CheckAccess(\Service\User::GROUP_USER);

        $this->set("D_OTV_LIST", \Service\OTV::getAll());

        $this->view();
    }

    public function EditAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $this->edit = true;
        $this->_tplfile = 'otv/add.html';
        return $this->AddAction();
    }

    public function AddAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $otv = new \Service\OTV($this->getEM());

        $DomainsList = \Service\VlanDomains::listDomains();

        if ($this->req->request->getInt('OTVID') != null) {
            $this->edit = (!empty($otv->selectByID($this->req->request->getInt('OTVID')))) ? true : false;
        } else {
            $this->edit = false;
        }

        $this->set("D_MODE", ($this->edit) ? "edit" : "add");

        $domains = array();

        if ($this->edit) {
            $DomainsSelected = $otv->getOTVDomains();

            $this->set(array(
                "D_OTV_ID" =>  $otv->getOTVID(),
                "D_OTV_NAME"   =>  $otv->getOTVName(),
                "D_OTV_DESCRIPTION" =>  $otv->getOTVDescription(),
            ));
        } else {
            $DomainsSelected =  $this->req->request->get('OTVDomains');

            $this->set(array(
                "D_OTV_ID" =>  $this->req->request->getInt('OTVID'),
                "D_OTV_NAME"   =>  $this->req->request->get('OTVName'),
                "D_OTV_DESCRIPTION" =>  $this->req->request->get('OTVDescription'),
            ));
        }

        foreach ($DomainsList as $data) {
            $domains[]= array(
                "domain_id" =>  $data['domain_id'],
                "domain_name"   =>  $data['domain_name'],
                "domain_selected"   =>  (($DomainsSelected != null) && in_array($data['domain_id'], $DomainsSelected)) ? true : false,
            );
        }

        $this->set("D_OTV_DOMAINS", $domains);

        if ($this->req->request->getBoolean('submitForm1') &&
            (empty($this->req->request->get('OTVName')))) {
            \MessageHandler::Warning("Leere Felder", "Bitte alle Felder ausfüllen.");
            return $this->view();
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            $otv->setOTVName($this->req->request->get('OTVName'));
            $otv->setOTVDescription($this->req->request->get('OTVDescription'));
            $otv->setOTVDomains(($this->req->request->get('OTVDomains') != null) ? $this->req->request->get('OTVDomains') : array());

            if ($otv->save()) {
                \MessageHandler::Success("OTV Instanz gespeichert", sprintf("Die OTV Instanz <strong>%s</strong> wurde gespeichert", $otv->getOTVName()));
                $this->_tplfile = 'otv/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error("Fehler", "Die OTV Instanz konnte nicht gespeichert werden.");
            }
        }

        $this->view();
    }

    public function DeleteAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $otv = new \Service\OTV($this->getEM());
        $otvData = $otv->selectByID($this->req->request->getInt("OTVID"));

        if (empty($otvData)) {
            \MessageHandler::Warning("OTV Instanz gibt es nicht", "Die OTV Instanz gibt es scheinbar nicht. Da kann ich nichts machen.");
            $this->_tplfile = 'otv/index.html';
            return $this->IndexAction();
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            if ($otv->delete()) {
                \MessageHandler::Success("OTV Instanz", "Die OTV Instanz wurde gelöscht.");
                $this->_tplfile = 'otv/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
            }
        }
        $this->set("D_OTV", $otvData[0]);

        $this->view();
    }
}
