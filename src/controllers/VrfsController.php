<?php
namespace Controller;

/**
 * VrfsController.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 12:17
 */
class VrfsController extends BaseController
{
    private $edit = false;

    public function IndexAction()
    {
        $this->CheckAccess(\Service\User::GROUP_USER);

        $this->set("D_VRF_LIST", \Service\VRF::getAll());

        return $this->view();
    }

    public function EditAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $this->edit = true;
        $this->_tplfile = 'vrfs/add.html';
        return $this->AddAction();
    }

    public function AddAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $VRF = new \Service\VRF($this->em);

        if ($this->req->request->get('VRFID') && $VRF->getbyID($this->req->request->get('VRFID')) !== false) {
            $this->edit = true;
            $this->set(array(
                "D_MODE"    =>  "edit",
                "D_VRFName" =>  $VRF->getEntity()->getVrfname(),
                "D_VRFDescription"  =>  $VRF->getEntity()->getVrfdescription(),
                "D_VRFID"   =>  $VRF->getEntity()->getVrfid(),
                "D_VRFRT"   =>  $VRF->getEntity()->getVrfrt(),
                "D_VRFRD"   =>  $VRF->getEntity()->getVrfrd(),
            ));
        } else {
            $this->edit = false;
            $this->set("D_MODE", "add");
        }

        if ($this->req->request->get('submitForm1') != null) {
            if ($this->req->request->get('VRFName') == null) {
                \MessageHandler::Warning("Bitte notwendige Felder ausfüllen", "Bitte wenigstens einen VRF Namen angeben.");
                $this->set(array(
                    "D_VRFName" =>  $this->req->request->get('VRFName'),
                    "D_VRFDescription"  =>  $this->req->request->get('VRFDescription'),
                    "D_VRFRT"   =>  $this->req->request->get('VRFRT'),
                    "D_VRFRD"   =>  $this->req->request->get('VRFRD'),
                ));

                return $this->view();
            }

            $VRF->getEntity()->setVrfdescription($this->req->get('VRFDescription'));
            $VRF->getEntity()->setVrfname($this->req->get('VRFName'));
            $VRF->getEntity()->setVrfrd($this->req->get('VRFRD'));
            $VRF->getEntity()->setVrfrt($this->req->get('VRFRT'));

            if ($VRF->save() === false) {
                $this->set(array(
                    "D_VRFName" =>  $this->req->get('VRFName'),
                    "D_VRFDescription"  =>  $this->req->get('VRFDescription'),
                    "D_VRFRT"   =>  $this->req->get('VRFRT'),
                    "D_VRFRD"   =>  $this->req->get('VRFRD'),
                ));
                if (!$this->edit) {
                    \MessageHandler::Error("Anlagefehler", "Beim Anlegen der VRF ist ein Fehler aufgetreten.");
                } else {
                    \MessageHandler::Error("Bearbeitungsfehler", "Beim Bearbeiten der VRF ist ein Fehler aufgetreten.");
                }
            } else {
                if (!$this->edit) {
                    \MessageHandler::Success("VRF angelegt", "Die neue VRF wurde angelegt.");
                } else {
                    \MessageHandler::Success("VRF bearbeitet", "Die VRF wurde bearbeitet.");
                }

                $this->_tplfile = 'vrfs/index.html';
                return $this->IndexAction();
            }
        }

        return $this->view();
    }

    public function DeleteAction()
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $vrf = new \Service\VRF($this->em);
        $vrf->getByID($this->req->request->getInt('VRFID'));

        if ($vrf->getEntity() == null) {
            \MessageHandler::Warning("VRF existiert nicht", "Die ausgewählte VRF existiert nicht.");
            $this->_tplfile = 'vrfs/index.html';
            return $this->IndexAction();
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            if ($vrf->delete()) {
                \MessageHandler::Success("VRF gelöscht", "Die VRF wurde gelöscht.");
                $this->_tplfile = 'vrfs/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error("Ooops!", "Da ist etwas schief gelaufen. Da muss man mal gucken.");
            }
        }


        $this->set("D_VRF", $vrf->getEntity());

        return $this->view();
    }
}
