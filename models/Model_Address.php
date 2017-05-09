<?php

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

    /**
     * @return array
     */
    public static function listAddresses(): array {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('*')
            ->from('addresses')
            ->execute()
            ->fetchAll();

        return (array)$select;

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
     * @return int
     */
    public function getAddress(): int
    {
        return $this->Address;
    }

    /**
     * @param int $Address
     */
    public function setAddress(int $Address)
    {
        $this->Address = $Address;
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




}