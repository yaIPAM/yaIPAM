<?php

/**
 * Model_VRF.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 12:28
 */
class Model_VRF {

	private $VRFID = 0;
	private $VRFName = "";
	private $VRFDescription = "";
	private $VRFRD = "";
	private $VRFRT = "";


	public static function getAll(): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$vrfs = $queryBuilder
			->select('VRFID', 'VRFName', 'VRFDescription', 'VRFRD', 'VRFRT')
			->from('vrfs')
			->orderBy('VRFName')
			->execute()
			->fetchAll();

		return $vrfs;
	}


	public function save(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		if ($this->getVRFID() == 0) {
			$dbal->beginTransaction();

			$insert = $queryBuilder
				->insert('vrfs')
				->setValue('VRFName', ':VRFName')
				->setValue('VRFDescription', ':VRFDescription')
				->setValue('VRFRT', ':VRFRT')
				->setValue('VRFRD', ':VRFRD')
				->setParameter('VRFName', $this->getVRFName())
				->setParameter('VRFDescription', $this->getVRFDescription())
				->setParameter('VRFRT', $this->getVRFRT())
				->setParameter('VRFRD', $this->getVRFRD());

			if ($insert->execute() === false) {
				$dbal->rollBack();
				return false;
			}

			$this->setVRFID($dbal->lastInsertId());

			$Subnet = new Model_Subnet();

			$RootPrefix = \IPLib\Range\Subnet::fromString('0.0.0.0/0');
			$Subnet->setAFI($RootPrefix->getAddressType());
			$Subnet->setPrefix('0.0.0.0');
			$Subnet->setPrefixDescription('IPv4 Root Prefix');
			$Subnet->setPrefixLength('0');
			$Subnet->setPrefixVRF(array($this->getVRFID()));
			$Subnet->setRangeFrom($RootPrefix->getComparableStartString());
			$Subnet->setRangeTo($RootPrefix->getComparableEndString());

			if ($Subnet->save() === false) {
				$dbal->rollBack();
				return false;
			}

			$Subnet = new Model_Subnet();

			$RootPrefix = \IPLib\Range\Subnet::fromString('::/0');
			$Subnet->setAFI($RootPrefix->getAddressType());
			$Subnet->setPrefix('::');
			$Subnet->setPrefixDescription('IPv6 Root Prefix');
			$Subnet->setPrefixLength('0');
			$Subnet->setPrefixVRF(array($this->getVRFID()));
			$Subnet->setRangeFrom($RootPrefix->getComparableStartString());
			$Subnet->setRangeTo($RootPrefix->getComparableEndString());

			if ($Subnet->save() === false) {
				$dbal->rollBack();
				return false;
			}


		}
		else if ($this->getVRFID() > 0) {
			$update = $queryBuilder
				->update('vrfs')
				->set('VRFName', ':VRFName')
				->set('VRFDescription', ':VRFDescription')
				->set('VRFRT', ':VRFRT')
				->set('VRFRD', ':VRFRD')
				->where('VRFID', ':VRFID')
				->setParameter('VRFName', $this->getVRFName())
				->setParameter('VRFDescription', $this->getVRFDescription())
				->setParameter('VRFRT', $this->getVRFRT())
				->setParameter('VRFRD', $this->getVRFRD())
				->setParameter('VRFID', $this->getVRFID());

			if ($update->execute() === false) {
				return false;
			}

			return true;
		}

		return false;
	}



	/**
	 * @return int
	 */
	public function getVRFID(): int {
		return $this->VRFID;
	}

	/**
	 * @param int $VRFID
	 */
	public function setVRFID(int $VRFID) {
		$this->VRFID = $VRFID;
	}

	/**
	 * @return string
	 */
	public function getVRFName(): string {
		return $this->VRFName;
	}

	/**
	 * @param string $VRFName
	 */
	public function setVRFName(string $VRFName) {
		$this->VRFName = $VRFName;
	}

	/**
	 * @return string
	 */
	public function getVRFDescription(): string {
		return $this->VRFDescription;
	}

	/**
	 * @param string $VRFDescription
	 */
	public function setVRFDescription(string $VRFDescription) {
		$this->VRFDescription = $VRFDescription;
	}

	/**
	 * @return int
	 */
	public function getVRFRD(): string {
		return $this->VRFRD;
	}

	/**
	 * @param int $VRFRD
	 */
	public function setVRFRD(string $VRFRD) {
		$this->VRFRD = $VRFRD;
	}

	/**
	 * @return string
	 */
	public function getVRFRT(): string {
		return $this->VRFRT;
	}

	/**
	 * @param string $VRFRT
	 */
	public function setVRFRT(string $VRFRT) {
		$this->VRFRT = $VRFRT;
	}

}