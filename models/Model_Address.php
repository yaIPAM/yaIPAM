<?php

// @TODO Move this to an entity

/**
 * Created by PhpStorm.
 * User: ktammling
 * Date: 09.05.17
 * Time: 14:41
 */
class Model_Address
{
    private $AddressID = 0;
    private $Address = 0;
    private $AddressAFI = 0;
    private $AddressState = 0;
    private $AddressName = "";
    private $AddressFQDN = "";
    private $AddressMAC = "";
    private $AddressTT = "";
    private $AddressDescription = "";
    private $AddressPrefix = 0;


    /**
     * @return bool
     */
    public function save(): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        if ($this->getAddressID() == 0) {

            $queryBuilder
                ->insert('addresses')
                ->setValue('Address', ':Address')
                ->setValue('AddressAFI', ':AddressAFI')
                ->setValue('AddressState', ':AddressState')
                ->setValue('AddressName', ':AddressName')
                ->setValue('AddressFQDN', ':AddressFQDN')
                ->setValue('AddressMAC', ':AddressMAC')
                ->setValue('AddressTT', ':AddressTT')
                ->setValue('AddressDescription', ':AddressDescription')
                ->setValue('AddressPrefix', ':AddressPrefix');


        }
        else {
            $queryBuilder
                ->update('addresses')
                ->set('Address', ':Address')
                ->set('AddressAFI', ':AddressAFI')
                ->set('AddressState', ':AddressState')
                ->set('AddressName', ':AddressName')
                ->set('AddressFQDN', ':AddressFQDN')
                ->set('AddressMAC', ':AddressMAC')
                ->set('AddressTT', ':AddressTT')
                ->set('AddressDescription', ':AddressDescription')
                ->set('AddressPrefix', ':AddressPrefix')
                ->where('AddressID = :AddressID')
                ->setParameter('AddressID', $this->getAddressID());
        }

        $queryBuilder
            ->setParameter('Address', $this->getAddress(true))
            ->setParameter('AddressAFI', $this->getAddressAFI())
            ->setParameter('AddressState', $this->getAddressState())
            ->setParameter('AddressName', $this->getAddressName())
            ->setParameter('AddressFQDN', $this->getAddressFQDN())
            ->setParameter('AddressMAC', $this->getAddressMAC())
            ->setParameter('AddressTT', $this->getAddressTT())
            ->setParameter('AddressDescription', $this->getAddressDescription())
            ->setParameter('AddressPrefix', $this->getAddressPrefix());

        if ($queryBuilder->execute() === false) {
            return false;
        }

        return true;


    }

    public function getAddressByID(int $AddressID): array {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('*')
            ->from('addresses')
            ->where('AddressID = :AddressID')
            ->setParameter('AddressID', $AddressID)
            ->execute()
            ->fetch();


        $this->setAddressID($select['AddressID']);
        $this->setAddressAFI($select['AddressAFI']);
        $this->setAddress($select['Address'], true);
        $this->setAddressState($select['AddressState']);
        $this->setAddressName($select['AddressName']);
        $this->setAddressFQDN($select['AddressFQDN']);
        $this->setAddressMAC($select['AddressMAC']);
        $this->setAddressTT($select['AddressTT']);
        $this->setAddressDescription($select['AddressDescription']);
        $this->setAddressPrefix($select['AddressPrefix']);


        return (array)$select;
    }

    public function delete(): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $queryBuilder
            ->delete('addresses')
            ->where('AddressID = :AddressID')
            ->setParameter('AddressID', $this->getAddressID());

        if ($queryBuilder->execute() === false) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function deleteByPrefix(int $PrefixID): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $queryBuilder
            ->delete('addresses')
            ->where('AddressPrefix = :PrefixID')
            ->setParameter('PrefixID', $PrefixID);

        if ($queryBuilder->execute() === false) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function calcNewPrefix(int $OldPrefixID, int $VRF, int $AFI) {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('AddressID', 'Address', 'AddressAFI')
            ->from('addresses')
            ->where('AddressPrefix = :AddressPrefix')
            ->setParameter('AddressPrefix', $OldPrefixID)
            ->execute()
            ->fetchAll();

        foreach ($select as $data) {

            $queryBuilder = $dbal->createQueryBuilder();
            $Address = ($data['AddressAFI'] == 4) ? long2ip($data['Address']) : long2ip6($data['Address']);
            $NewPrefixID = self::getParentID($Address, $VRF, $AFI);

            $queryBuilder
                ->update('addresses')
                ->set('AddressPrefix', $NewPrefixID)
                ->where('AddressID = :AddressID')
                ->setParameter('AddressID', $data['AddressID']);

            if ($queryBuilder->execute() === false) {
                return false;
            }
        }

        return true;

    }

    public static function AddressExists(string $Address, int $VRF): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $AFI = IPLib\Factory::addressFromString($Address);

        $select = $queryBuilder
            ->select('COUNT(*) AS total')
            ->from('addresses', 'a')
            ->innerjoin('a', 'prefixes', 'p', 'a.AddressPrefix = p.PrefixID')
            ->where('AddressAFI = :AddressAFI')
            ->andWhere('p.MasterVRF = :MasterVRF')
            ->andWhere('Address = :Address')
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AddressAFI', $AFI->getAddressType())
            ->setParameter('Address', ($AFI->getAddressType()==4) ? ip2long($Address) : ip2long6($Address))
            ->execute()
            ->fetch();

        if ($select['total'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function listAddresses(int $PrefixID): array {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('*')
            ->from('addresses')
            ->where('AddressPrefix = :PrefixID')
            ->setParameter('PrefixID', $PrefixID)
            ->orderBy('Address', 'ASC')
            ->execute()
            ->fetchAll();

        return (array)$select;

    }

    /**
     * @param string $Address
     * @param int $VRF
     * @return int
     */
    public static function getParentID(string $Address, int $VRF, int $AFI): int {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('PrefixID')
            ->from('prefixes')
            ->where(':address between RangeFrom and RangeTo')
            ->andWhere('MasterVRF = :MasterVRF')
            ->andWhere('AFI = :AFI')
            ->orderBy('PrefixLength', 'DESC')
            ->setParameter('address', IPLib\Factory::addressFromString($Address)->getComparableString())
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AFI', $AFI)
            ->execute()
            ->fetch();

        return (int)$select['PrefixID'];
    }

    /**
     * @return int
     */
    public function getAddressAFI(): int
    {
        return $this->AddressAFI;
    }

    /**
     * @param int $AddressAFI
     */
    public function setAddressAFI(int $AddressAFI)
    {
        $this->AddressAFI = $AddressAFI;
    }

    /**
     * @return int
     */
    public function getAddressID(): int
    {
        return $this->AddressID;
    }

    /**
     * @param int $AddressID
     */
    public function setAddressID(int $AddressID)
    {
        $this->AddressID = $AddressID;
    }

    /**
     * @param bool $long
     * @return string
     */
    public function getAddress(bool $long = false): string
    {

        if ($long) {
            return $this->Address;
        }

        if ($this->getAddressAFI() == 4) {
            return long2ip($this->Address);
        }
        else if ($this->getAddressAFI() == 6) {
            return long2ip6($this->Address);
        }
    }

    /**
     * @param string $Address
     */
    public function setAddress(string $Address, bool $long = false)
    {

        if ($long) {
            $this->Address = $Address;
        }
        else {
            $AFI = IPLib\Factory::addressFromString($Address);

            $Address = ($AFI->getAddressType() == 4) ? ip2long($Address) : ip2long6($Address);

            $this->setAddressAFI($AFI->getAddressType());

            $this->Address = $Address;
        }
    }

    /**
     * @return int
     */
    public function getAddressState(): int
    {
        return $this->AddressState;
    }

    /**
     * @param int $AddressState
     */
    public function setAddressState(int $AddressState)
    {
        $this->AddressState = $AddressState;
    }

    /**
     * @return string
     */
    public function getAddressName(): string
    {
        return $this->AddressName;
    }

    /**
     * @param string $AddressName
     */
    public function setAddressName(string $AddressName)
    {
        $this->AddressName = $AddressName;
    }

    /**
     * @return string
     */
    public function getAddressFQDN(): string
    {
        return $this->AddressFQDN;
    }

    /**
     * @param string $AddressFQDN
     */
    public function setAddressFQDN(string $AddressFQDN)
    {
        $this->AddressFQDN = $AddressFQDN;
    }

    /**
     * @return string
     */
    public function getAddressMAC(): string
    {
        return $this->AddressMAC;
    }

    /**
     * @param string $AddressMAC
     */
    public function setAddressMAC(string $AddressMAC)
    {
        $this->AddressMAC = $AddressMAC;
    }

    /**
     * @return string
     */
    public function getAddressTT(): string
    {
        return $this->AddressTT;
    }

    /**
     * @param string $AddressTT
     */
    public function setAddressTT(string $AddressTT)
    {
        $this->AddressTT = $AddressTT;
    }

    /**
     * @return string
     */
    public function getAddressDescription(): string
    {
        return $this->AddressDescription;
    }

    /**
     * @param string $AddressDescription
     */
    public function setAddressDescription(string $AddressDescription)
    {
        $this->AddressDescription = $AddressDescription;
    }

    /**
     * @return int
     */
    public function getAddressPrefix(): int
    {
        return $this->AddressPrefix;
    }

    /**
     * @param int $AddressPrefix
     */
    public function setAddressPrefix(int $AddressPrefix)
    {
        $this->AddressPrefix = $AddressPrefix;
    }
}