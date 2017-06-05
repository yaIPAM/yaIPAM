<?php
namespace Controller;

/**
 * vlandomains.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.04.17
 * Time: 11:53
 */
class VlandomainsController extends BaseController
{
    private $edit = false;

    public function IndexAction()
    {
        $this->CheckAccess(\Service\User::GROUP_USER);

        $this->set("D_VLAN_DOMAINS", \Service\VlanDomains::listDomains());

        return $this->view();
    }

    public function AddAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $VlanDomain = new \Service\VlanDomains($this->getEM());

        if ($this->req->request->getInt('ID') != null) {
            $this->edit = (!empty($VlanDomain->selectByID($this->req->request->getInt('ID')))) ? true : false;
        } else {
            $this->edit = false;
        }

        $this->set("D_MODE", ($this->edit) ? "edit" : "add");

        if ($this->edit) {
            $this->set(array(
                "D_DOMAIN_ID" =>  $VlanDomain->getEntity()->getDomainID(),
                "D_DOMAIN_NAME"   =>  $VlanDomain->getEntity()->getDomainName(),
                "D_DOMAIN_DESCRIPTION" =>  $VlanDomain->getEntity()->getDomainDescription(),
            ));
        } else {
            $this->set(array(
                "D_DOMAIN_ID" =>  $this->req->request->getInt('ID'),
                "D_DOMAIN_NAME"   =>  $this->req->request->get('DomainName'),
                "D_DOMAIN_DESCRIPTION" =>  $this->req->request->get('DomainDescription'),
            ));
        }

        if ($this->req->request->getBoolean('submitForm1') &&
            (empty($this->req->request->get('DomainName')) or
            empty($this->req->request->get('DomainDescription')))) {
            \MessageHandler::Warning("Leere Felder", "Bitte alle Felder ausfüllen.");
            return $this->view();
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            $VlanDomain->getEntity()->setDomainName($this->req->request->get('DomainName'));
            $VlanDomain->getEntity()->setDomainDescription($this->req->request->get('DomainDescription'));

            if ($VlanDomain->save()) {
                \MessageHandler::Success("Domain gespeichert", sprintf("Die Domain <strong>%s</strong> wurde gespeichert", $VlanDomain->getEntity()->getDomainName()));
                $this->_tplfile = 'vlandomains/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error("Fehler", "Die Domain konnte nicht gespeichert werden.");
            }
        }

        return $this->view();
    }

    public function EditAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $this->edit = true;
        $this->_tplfile = 'vlandomains/add.html';
        return $this->AddAction();
    }

    public function DeleteAction()
    {
        global $dbal;

        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $dbal->beginTransaction();
        $VlanDomain = new \Service\VlanDomains($this->getEM());
        $DomainData = $VlanDomain->selectByID($this->req->request->getInt("ID"));

        if (empty($DomainData)) {
            \MessageHandler::Warning("VLAN Domain existiert nicht", "Die Vlan Domain existiert nicht.");
            $this->_tplfile = 'vlandomains/index.html';
            return $this->IndexAction();
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            if ($VlanDomain->delete()) {
                \MessageHandler::Success("Domain gelöscht", "Die Domain und alle dazugehörigen VLANs wurde gelöscht.");
                $dbal->commit();
                $this->_tplfile = 'vlandomains/index.html';
                return $this->IndexAction();
            } else {
                $dbal->rollBack();
                \MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
            }
        }

        $this->set("D_VLAN_COUNT", \Service\Vlans::CountAllByDomain($VlanDomain->getEntity()->getDomainID()));
        $this->set("D_DOMAIN", $DomainData);

        return $this->view();
    }
}
