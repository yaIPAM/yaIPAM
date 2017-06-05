<?php
namespace Controller;

/**
 * addresses.php
 * Project: yaipam
 * User: ktammling
 * Date: 23.04.17
 * Time: 11:13
 */
class AddressesController extends BaseController
{
    private $CurrentSubnet;
    private $CurrentVRF;
    private $edit = false;

    public function IndexAction()
    {
        $this->CheckAccess(\Service\User::GROUP_USER);

        $this->set("D_VRF_LIST", \Service\VRF::getWithRoot());

        return $this->view();
    }

    public function AddressdeleteAction($SubnetID = null, $AddressID = null)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $Address = new \Service\Addresses($this->em);
        if (!($Address->getAddressByID($AddressID))) {
            \MessageHandler::Error(_('IP does not exist'), _('The selected IP address does not exist.'));
            $this->_tplfile = 'addresses/subnet.html';
            return $this->SubnetAction($SubnetID);
        }

        if ($this->req->request->getBoolean('submitForm1')) {
            if ($Address->delete() === false) {
                \MessageHandler::Error(_('Could not delete IP'), _('An error occured while deleting the IP.'));
                $this->_tplfile = 'addresses/subnet.html';
                return $this->SubnetAction($SubnetID);
            } else {
                \MessageHandler::Success(_('IP deleted'), _('The IP has been deleted'));
                $this->_tplfile = 'addresses/subnet.html';
                return $this->SubnetAction($SubnetID);
            }
        }

        $this->set(array(
            "D_PrefixID"    =>  $SubnetID,
            "D_Address" =>  $Address->getEntity()->getAddress(),
            "D_AddressID"   =>  $AddressID,
        ));

        $this->view();
    }

    public function AddresseditAction($SubnetID = null, $AddressID = null)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $this->setCurrentSubnet($SubnetID);
        $this->_tplfile = 'addresses/addressadd.html';
        $this->edit = true;
        return $this->AddressaddAction($SubnetID, $AddressID);
    }

    public function AddressaddAction($SubnetID = null, $AddressID = null, $Address = null)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $Prefix = new \Service\Prefixes($this->em);
        if ($SubnetID == null or empty($Prefix->getByID($SubnetID))) {
            \MessageHandler::Error(_('Prefix not existing'), _('The selected Prefix does not exist.'));

            $this->_tplfile = 'addresses/index.html';
            return $this->IndexAction();
        }

        if ($AddressID == 0 && $Address != null) {
            $this->set('D_Address', $Address);
        }

        $this->set("D_PrefixID", $Prefix->getEntity()->getPrefixid());

        $Address = new \Service\Addresses($this->em);

        if ($this->edit) {
            $Address->getAddressByID($AddressID);
        }

        if ($this->edit && !empty($Address)) {
            $this->set(array(
                "D_MODE"    =>  "edit",
                "D_AddressID"   =>  $Address->getEntity()->getAddressID(),
                "D_Address" =>  $Address->getEntity()->getAddress(),
                "D_AddressName" =>  $Address->getEntity()->getAddressName(),
                "D_AddressFQDN" =>  $Address->getEntity()->getAddressFQDN(),
                "D_AddressState"    =>  $Address->getEntity()->getAddressState(),
                "D_AddressMAC"  =>  $Address->getEntity()->getAddressMAC(),
                "D_AddressTT"   =>  $Address->getEntity()->getAddressTT(),
                "D_AddressDescription"  =>  $Address->getEntity()->getAddressDescription(),
            ));
        } else {
            $this->set(array(
                "D_MODE"    =>  "add",
            ));
        }


        $error = false;
        if ($this->req->request->get('submitForm1') != null) {
            if ($this->req->request->get('Address') == null or \IPLib\Factory::addressFromString($this->req->request->get('Address')) == false) {
                \MessageHandler::Error(_('Invalid address'), _('The supplied IP address is invalid.'));
                $error = true;
            }

            if (!$this->edit && \Service\Addresses::AddressExists($this->req->request->get('Address'), $Prefix->getEntity()->getMasterVRF())) {
                \MessageHandler::Warning(_('Address existing'), _('The IP address already exists'));
                $error = true;
            }

            if ($error) {
                $this->set(array(
                    "D_Address" =>  $this->req->request->get('Address'),
                    "D_AddressName" =>  $this->req->request->get('AddressName'),
                    "D_AddressFQDN" =>  $this->req->request->get('AddressFQDN'),
                    "D_AddressState"    =>  $this->req->request->get('AddressState'),
                    "D_AddressMAC"  =>  $this->req->request->get('AddressMAC'),
                    "D_AddressTT"   =>  $this->req->request->get('AddressTT'),
                    "D_AddressDescription"  =>  $this->req->request->get('AddressDescription'),
                ));

                return $this->view();
            }

            if (!$this->edit) {
                $NewAddress = new \Service\Addresses($this->em);
                $Address = \IPLib\Factory::addressFromString($this->req->request->get('Address'));
                $NewAddress->getEntity()->setAddress($this->req->request->get('Address'));
                $NewAddress->getEntity()->setAddressAFI($Address->getAddressType());
                $NewAddress->getEntity()->setAddressPrefix($NewAddress::getParentID($this->req->request->get('Address'), $Prefix->getEntity()->getMasterVRF(), $Address->getAddressType()));
            } else {
                $NewAddress = $Address;
                $NewAddress->getEntity()->setAddressPrefix($NewAddress::getParentID($this->req->request->get('Address'), $Prefix->getEntity()->getMasterVRF(), $Address->getEntity()->getAddressAFI()));
            }

            $NewAddress->getEntity()->setAddressDescription($this->req->request->get('AddressDescription'));
            $NewAddress->getEntity()->setAddressFQDN($this->req->request->get('AddressFQDN'));
            $NewAddress->getEntity()->setAddressMAC($this->req->request->get('AddressMAC'));
            $NewAddress->getEntity()->setAddressName($this->req->request->get('AddressName'));
            $NewAddress->getEntity()->setAddressState($this->req->request->get('AddressState'));
            $NewAddress->getEntity()->setAddressTT($this->req->request->get('AddressTT'));


            if ($NewAddress->save() === false) {
                \MessageHandler::Error(_('Error'), _('Error while saving IP address'));
                $this->_tplfile = 'addresses/subnet.html';
                return $this->SubnetAction($SubnetID);
            } else {
                \MessageHandler::Success(_('Saved'), _('IP address has been saved.'));
                $this->_tplfile = 'addresses/subnet.html';
                return $this->SubnetAction($SubnetID);
            }
        }

        return $this->view();
    }

    public function SubneteditAction(int $SubnetID)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $this->setCurrentSubnet($SubnetID);
        $this->_tplfile = 'addresses/subnetadd.html';
        $this->edit = true;
        return $this->SubnetaddAction();
    }

    /**
     * @param null $SubnetID
     * @param null $Prefix
     * @param null $PrefixLength
     * @return bool
     */
    public function SubnetaddAction($SubnetID = null, $Prefix = null, $PrefixLength = null)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        if ($SubnetID == null) {
            $SubnetID = $this->getCurrentSubnet();
        }

        if ($PrefixLength != null && $Prefix != null) {
            $this->set("D_PrefixName", $Prefix.'/'.$PrefixLength);
        }


        $CurrentSubnet = new \Service\Prefixes($this->em);
        $CurrentSubnet->getByID($SubnetID);


        if (!empty($CurrentSubnet) && $this->edit) {
            $this->set(array(
                "D_MODE"    =>  "edit",
                "D_PrefixID"    =>  $CurrentSubnet->getEntity()->getPrefixID(),
                "D_PrefixName"  =>  $CurrentSubnet->getEntity()->getPrefix()."/".$CurrentSubnet->getEntity()->getPrefixLength(),
                "D_PrefixState" =>  $CurrentSubnet->getEntity()->getPrefixState(),
                "D_PrefixDescription"   =>  $CurrentSubnet->getEntity()->getPrefixDescription(),
                "D_PrefixVLAN"  =>  $CurrentSubnet->getEntity()->getPrefixVLAN(),
            ));
        }

        $VRF = \Service\VRF::getAllExcept($CurrentSubnet->getEntity()->getMasterVRF());

        $this->set(array(
            "D_PrefixID"    =>  $SubnetID,
            "D_VRFS"    =>  $VRF,
            "D_VLANS"   =>  \Service\Vlans::getAll(),
        ));

        if ($this->req->request->getBoolean('Submit')) {
            if (\IPLib\Range\Subnet::fromString($this->req->request->get('PrefixName')) ==  null) {
                \MessageHandler::Error(_('Invalid prefix'), _('The entered prefix is invalid.'));

                $this->set(array(
                    "D_PrefixName"  =>  $this->req->request->get('PrefixName'),
                    "D_PrefixState" =>  $this->req->request->get('PrefixState'),
                    "D_PrefixDescription"   =>  $this->req->request->get('PrefixDescription'),
                    "D_PrefixVLAN"  =>  $this->req->request->getInt('PrefixVLAN'),
                ));

                return $this->view();
            }

            if (!$this->edit && \Service\Prefixes::PrefixExists($this->req->request->get('PrefixName'), $CurrentSubnet->getEntity()->getMasterVRF())) {
                \MessageHandler::Error(_('Prefix already exists'), _('The entered prefix already exists.'));

                $this->set(array(
                    "D_PrefixName"  =>  $this->req->request->get('PrefixName'),
                    "D_PrefixState" =>  $this->req->request->get('PrefixState'),
                    "D_PrefixDescription"   =>  $this->req->request->get('PrefixDescription'),
                    "D_PrefixVLAN"  =>  $this->req->request->getInt('PrefixVLAN'),
                ));

                return $this->view();
            }

            if ($this->req->request->get('PrefixName') == null) {
                \MessageHandler::Error(_('Empty field'), _('Please fill in all required fields'));

                $this->set([
                    "D_PrefixName"  =>  $this->req->request->get('PrefixName'),
                    "D_PrefixState" =>  $this->req->request->get('PrefixState'),
                    "D_PrefixDescription"   =>  $this->req->request->get('PrefixDescription'),
                    "D_PrefixVLAN"  =>  $this->req->request->getInt('PrefixVLAN'),
                ]);

                return $this->view();
            }

            if (!(\IPLib\Range\Subnet::fromString($this->req->request->get('PrefixName')))) {
                \MessageHandler::Error(_('Invalid Prefix'), _('This Prefix is invalid.'));

                $this->set(array(
                    "D_PrefixName"  =>  $this->req->request->get('PrefixName'),
                    "D_PrefixState" =>  $this->req->request->get('PrefixState'),
                    "D_PrefixDescription"   =>  $this->req->request->get('PrefixDescription'),
                    "D_PrefixVLAN"  =>  $this->req->request->getInt('PrefixVLAN'),
                ));

                return $this->view();
            }

            if (!$this->edit) {
                $ParentID = \Service\Prefixes::CalculateParentID($this->req->request->get('PrefixName'), $CurrentSubnet->getEntity()->getMasterVRF());
            }

            $PrefixName = $this->req->request->get('PrefixName');
            $PrefixName = explode("/", $PrefixName);
            $Prefix = \IPBlock::create($PrefixName[0], $PrefixName[1]);
            $PrefixCompare = \IPLib\Range\Subnet::fromString($PrefixName[0].'/'.$PrefixName[1]);

            if (!$this->edit) {
                $NewSubnet = new \Service\Prefixes($this->em);
                $NewSubnet->getEntity()->setParentID($ParentID);
            } else {
                $NewSubnet = $CurrentSubnet;
            }

            $NewSubnet->getEntity()->setPrefix($Prefix);
            $NewSubnet->getEntity()->setMasterVRF($CurrentSubnet->getEntity()->getMasterVRF());
            $NewSubnet->getEntity()->setPrefixDescription($this->req->request->get('PrefixDescription'));
            $NewSubnet->getEntity()->setRangeFrom($PrefixCompare->getComparableStartString());
            $NewSubnet->getEntity()->setRangeTo($PrefixCompare->getComparableEndString());
            $NewSubnet->getEntity()->setPrefixLength($PrefixName[1]);
            $NewSubnet->getEntity()->setAFI($Prefix->getVersion());
            $NewSubnet->getEntity()->setPrefixState($this->req->request->get('PrefixState'));
            $NewSubnet->getEntity()->setPrefixVLAN($this->req->request->get('PrefixVLAN'));

            if ($NewSubnet->save()) {
                \MessageHandler::Success(_('Prefix saved'), _('The prefix has been saved'));

                $this->_tplfile = 'addresses/subnet.html';
                return $this->SubnetAction($NewSubnet->getEntity()->getPrefixID());
            } else {
                \MessageHandler::Error(_('Prefix not saved'), _('Error saving the prefix'));

                $this->set(array(
                    "D_PrefixName"  =>  $this->req->request->get('PrefixName'),
                    "D_PrefixState" =>  $this->req->request->get('PrefixState'),
                    "D_PrefixDescription"   =>  $this->req->request->get('PrefixDescription'),
                    "D_PrefixVLAN"  =>  $this->req->request->getInt('PrefixVLAN'),
                ));

                return $this->view();
            }
        }

        $this->view();
    }


    public function SubnetdeleteAction($SubnetID)
    {
        $this->CheckAccess(\Service\User::GROUP_ADMINISTRATOR);

        $CurrentSubnet = new \Service\Prefixes($this->em);
        $CurrentSubnet->getByID($SubnetID);
        $this->setCurrentSubnet($SubnetID);

        if (empty($CurrentSubnet)) {
            \MessageHandler::Warning(_('Prefix not existing'), _('The selected prefix does not exist.'));
            $this->_tplfile = 'addresses/subnet.html';
            return $this->SubnetAction($SubnetID);
        }

        if ($this->req->request->getBoolean('submitForm1') && $this->req->request->getInt('DeleteOption') == 1) {
            if ($CurrentSubnet->delete(1)) {
                \MessageHandler::Success(_('Prefix deleted'), _('The prefix and all nested prefixes/addresses have been deleted.'));
                $this->_tplfile = 'addresses/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error(_("Ooops!"), _('Something unexpected went wrong!'));
            }
        } elseif ($this->req->request->getBoolean('submitForm1') && $this->req->request->getInt('DeleteOption') == 2) {
            if ($CurrentSubnet->delete(2)) {
                \MessageHandler::Success(_('Prefix deleted'), _('The prefix has been deleted'));
                $this->_tplfile = 'addresses/index.html';
                return $this->IndexAction();
            } else {
                \MessageHandler::Error(_("Ooops!"), _('Something unexpected went wrong!'));
            }
        }


        $this->set(array(
            "D_Prefix"  =>  $CurrentSubnet->getEntity()->getPrefix(),
            "D_PrefixLength"    =>  $CurrentSubnet->getEntity()->getPrefixLength(),
            "D_PrefixID"    =>  $CurrentSubnet->getEntity()->getPrefixID(),
        ));

        $this->view();
    }

    /**
     * @param null $SubnetID
     * @return bool
     */
    public function SubnetAction($SubnetID = null)
    {
        global $EntityManager;

        $this->CheckAccess(\Service\User::GROUP_USER);

        $this->setCurrentSubnet($SubnetID);

        if ($SubnetID !== null) {
            $ID = $SubnetID;
        } else {
            $ID = $this->getCurrentSubnet();
        }

        $Subnet = new \Service\Prefixes($EntityManager);
        $Subnet->getByID($ID);

        $VRF = new \Service\VRF($EntityManager);
        $VRF->getByID($Subnet->getEntity()->getMastervrf());
        $SubnetPrefix = $Subnet->getEntity()->getPrefix();
        $IPData = \IPBlock::create($SubnetPrefix."/".$Subnet->getEntity()->getPrefixlength());

        if ($Subnet->getEntity()->getPrefixvlan() == null or $Subnet->getEntity()->getPrefixvlan() == 0) {
            $Prefix = _('None');
        } else {
            $Vlan = new \Service\Vlans($this->em);
            $Vlan->get($Subnet->getEntity()->getPrefixvlan());

            $VlanDomain = new \Service\VlanDomains($this->em);
            $VlanDomain->selectByID($Vlan->getEntity()->getVlandomain());

            $Prefix = $VlanDomain->getEntity()->getDomainName().": ".($Vlan->getEntity() != null) ? $Vlan->getEntity()->getVlanID()." - ".$Vlan->getEntity()->getVlanName() : "";
        }




        $this->set([
            "D_PrefixID"    =>  $Subnet->getEntity()->getPrefixid(),
            "D_MasterVRF"   =>  $Subnet->getEntity()->getMastervrf(),
            "D_MasterVRF_Name"  =>  $VRF->getEntity()->getVrfname(),
            "D_AFI" =>  $Subnet->getEntity()->getAfi(),
            "D_Prefix"  =>  $SubnetPrefix,
            "D_RangeTo" =>  $IPData->getLastIp(),
            "D_RangeFrom"   =>  $IPData->getFirstIp(),
            "D_PrefixDescription"   =>  $Subnet->getEntity()->getPrefixdescription(),
            "D_PrefixLength"    =>  $Subnet->getEntity()->getPrefixlength(),
            "D_ParentID"    =>  $Subnet->getEntity()->getParentid(),
            "D_Subnets" =>  \Service\Prefixes::getSubPrefixes($Subnet->getEntity()->getPrefixid()),
            "D_Addresses"   =>  \Service\Addresses::listAddresses($Subnet->getEntity()->getPrefixid()),
            "D_Breadcrumbs"  =>  \service\Prefixes::createSubnetBreadcrumbs($ID),
            "D_NetworkMask" =>  $IPData->getMask(),
            "D_Broadcast_Address" => $IPData->getLastIp(),
            "D_Network_Address" => $IPData->getFirstIp(),
            "D_NetworkNumber_Addresses" =>  $IPData->getNbAddresses(),
            "D_Network_Wildcard"    =>  reverseNetmask($IPData->getMask()),
            "D_PrefixVLAN"  =>  $Prefix,
        ]);

        return $this->view();
    }

    /**
     * @return mixed
     */
    public function getCurrentSubnet()
    {
        return $this->CurrentSubnet;
    }

    /**
     * @param mixed $CurrentSubnet
     */
    public function setCurrentSubnet($CurrentSubnet)
    {
        $this->CurrentSubnet = $CurrentSubnet;
    }

    /**
     * @return mixed
     */
    public function getCurrentVRF()
    {
        return $this->CurrentVRF;
    }

    /**
     * @param mixed $CurrentVRF
     */
    public function setCurrentVRF($CurrentVRF)
    {
        $this->CurrentVRF = $CurrentVRF;
    }
}
