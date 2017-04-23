<?php

/**
 * Model_Subnet.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 13:24
 */
class Model_Subnet {

	private $PrefixID = 0;
	private $Prefix = "";
	private $PrefixLength = 0;
	private $AFI = 4;
	private $RangeFrom = "";
	private $RangeTo = "";
	private $PrefixDescription = "";
	private $PrefixVRF = array();
	private $ParentID = 0;
	private $MasterVRF = 0;

	public function save(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		if ($this->getPrefixID() == 0) {

			$dbal->beginTransaction();

			if ($this->getAFI() == 4) {
				$insert = $queryBuilder
					->insert('prefixes')
					->setValue('Prefix', ':Prefix')
					->setValue('PrefixLength', ':PrefixLength')
					->setValue('AFI', ':AFI')
					->setValue('PrefixDescription', ':PrefixDescription')
					->setValue('RangeFrom', ':RangeFrom')
					->setValue('RangeTo', ':RangeTo')
					->setValue('MasterVRF', ':MasterVRF')
					->setValue('ParentID', ':ParentID')
					->setParameter('Prefix', ip2long($this->getPrefix()))
					->setParameter('PrefixLength', $this->getPrefixLength())
					->setParameter('AFI', $this->getAFI())
					->setParameter('PrefixDescription', $this->getPrefixDescription())
					->setParameter('RangeFrom', $this->getRangeFrom())
					->setParameter('RangeTo', $this->getRangeTo())
					->setParameter('MasterVRF', $this->getMasterVRF())
					->setParameter('ParentID', $this->getParentID());
			}
			else if ($this->getAFI() == 6) {
				$insert = $queryBuilder
					->insert('prefixes')
					->setValue('Prefix', ':Prefix')
					->setValue('PrefixLength', ':PrefixLength')
					->setValue('AFI', ':AFI')
					->setValue('PrefixDescription', ':PrefixDescription')
					->setValue('RangeFrom', ':RangeFrom')
					->setValue('RangeTo', ':RangeTo')
					->setValue('MasterVRF', ':MasterVRF')
					->setValue('ParentID', ':ParentID')
					->setParameter('Prefix', ip2long6($this->getPrefix()))
					->setParameter('PrefixLength', $this->getPrefixLength())
					->setParameter('AFI', $this->getAFI())
					->setParameter('PrefixDescription', $this->getPrefixDescription())
					->setParameter('RangeFrom', $this->getRangeFrom())
					->setParameter('RangeTo', $this->getRangeTo())
					->setParameter('MasterVRF', $this->getMasterVRF())
					->setParameter('ParentID', $this->getParentID());
			}

			if ($insert->execute() === false) {
				$dbal->rollBack();
				return false;
			}

			$this->setPrefixID($dbal->lastInsertId());

			foreach ($this->getPrefixVRF() as $key => $value) {
				$queryBuilder = $dbal->createQueryBuilder();

				$insert = $queryBuilder
					->insert('prefixes_vrfs')
					->setValue('PrefixID', ':PrefixID')
					->setValue('VRFID', ':VRFID')
					->setParameter('PrefixID', $this->getPrefixID())
					->setParameter('VRFID', $value);

				if ($insert->execute() === false) {
					$dbal->rollBack();
					return false;
				}
			}

			$dbal->commit();
			return true;
		}
		else if ($this->getPrefixID() > 0) {
			$dbal->beginTransaction();

			if ($this->getAFI() == 4) {
				$update = $queryBuilder
					->update('prefixes')
					->set('Prefix', ':Prefix')
					->set('PrefixLength', ':PrefixLength')
					->set('AFI', ':AFI')
					->set('PrefixDescription', ':PrefixDescription')
					->set('RangeFrom', ':RangeFrom')
					->set('RangeTo', ':RangeTo')
					->set('MasterVRF', ':MasterVRF')
					->set('ParentID', ':ParentID')
					->where('PrefixID = :PrefixID')
					->setParameter('Prefix', ip2long($this->getPrefix()))
					->setParameter('PrefixLength', $this->getPrefixLength())
					->setParameter('AFI', $this->getAFI())
					->setParameter('PrefixDescription', $this->getPrefixDescription())
					->setParameter('RangeFrom', $this->getRangeFrom())
					->setParameter('RangeTo', $this->getRangeTo())
					->setParameter('PrefixID', $this->getPrefixID())
					->setParameter('MasterVRF', $this->getMasterVRF())
					->setParameter('ParentID', $this->getParentID());

			}
			else if ($this->getAFI() == 6) {
				$update = $queryBuilder
					->update('prefixes')
					->set('Prefix', ':Prefix')
					->set('PrefixLength', ':PrefixLength')
					->set('AFI', ':AFI')
					->set('PrefixDescription', ':PrefixDescription')
					->set('RangeFrom', ':RangeFrom')
					->set('RangeTo', ':RangeTo')
					->set('MasterVRF', ':MasterVRF')
					->set('ParentID', ':ParentID')
					->where('PrefixID = :PrefixID')
					->setParameter('Prefix', ip2long6($this->getPrefix()))
					->setParameter('PrefixLength', $this->getPrefixLength())
					->setParameter('AFI', $this->getAFI())
					->setParameter('PrefixDescription', $this->getPrefixDescription())
					->setParameter('RangeFrom', $this->getRangeFrom())
					->setParameter('RangeTo', $this->getRangeTo())
					->setParameter('PrefixID', $this->getPrefixID())
					->setParameter('MasterVRF', $this->getMasterVRF())
					->setParameter('ParentID', $this->getParentID());
			}

			if ($update->execute() === false) {
				$dbal->rollBack();
				return false;
			}

			$queryBuilder
				->delete('prefixes_vrfs')
				->where('PrefixID = :PrefixID')
				->setParameter('PrefixID', $this->getPrefixID())
				->execute();

			foreach ($this->getPrefixVRF() as $key => $value) {
				$queryBuilder = $dbal->createQueryBuilder();

				$insert = $queryBuilder
					->insert('prefixes_vrfs')
					->setValue('PrefixID', ':PrefixID')
					->setValue('VRFID', ':VRFID')
					->setParameter('PrefixID', $this->getPrefixID())
					->setParameter('VRFID', $value);

				if ($insert === false) {
					$dbal->rollBack();
					return false;
				}
			}

			$dbal->commit();
			return true;
		}

		return false;
	}

	/**
	 * @param int $VRFID
	 * @return bool
	 */
	public function deleteByVRF(int $VRFID): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$delete = $queryBuilder
			->delete('prefixes')
			->where('MasterVRF = :VRFID')
			->setParameter('VRFID', $VRFID);

		if ($delete->execute() === false) {
			return false;
		}

		$delete = $queryBuilder
			->delete('prefixes_vrfs')
			->where('VRFID = :VRFID')
			->setParameter('VRFID', $VRFID);

		if ($delete->execute() === false) {
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getPrefixID(): int {
		return $this->PrefixID;
	}

	/**
	 * @param int $PrefixID
	 */
	public function setPrefixID(int $PrefixID) {
		$this->PrefixID = $PrefixID;
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->Prefix;
	}

	/**
	 * @param string $Prefix
	 */
	public function setPrefix(string $Prefix) {
		$this->Prefix = $Prefix;
	}

	/**
	 * @return int
	 */
	public function getPrefixLength(): int {
		return $this->PrefixLength;
	}

	/**
	 * @param int $PrefixLength
	 */
	public function setPrefixLength(int $PrefixLength) {
		$this->PrefixLength = $PrefixLength;
	}

	/**
	 * @return int
	 */
	public function getAFI(): int {
		return $this->AFI;
	}

	/**
	 * @param int $AFI
	 */
	public function setAFI(int $AFI) {
		$this->AFI = $AFI;
	}

	/**
	 * @return string
	 */
	public function getRangeFrom(): string {
		return $this->RangeFrom;
	}

	/**
	 * @param string $RangeFrom
	 */
	public function setRangeFrom(string $RangeFrom) {
		$this->RangeFrom = $RangeFrom;
	}

	/**
	 * @return string
	 */
	public function getRangeTo(): string {
		return $this->RangeTo;
	}

	/**
	 * @param string $RangeTo
	 */
	public function setRangeTo(string $RangeTo) {
		$this->RangeTo = $RangeTo;
	}

	/**
	 * @return string
	 */
	public function getPrefixDescription(): string {
		return $this->PrefixDescription;
	}

	/**
	 * @param string $PrefixDescription
	 */
	public function setPrefixDescription(string $PrefixDescription) {
		$this->PrefixDescription = $PrefixDescription;
	}

	/**
	 * @return array
	 */
	public function getPrefixVRF(): array {
		return $this->PrefixVRF;
	}

	/**
	 * @param array $PrefixVRF
	 */
	public function setPrefixVRF(array $PrefixVRF) {
		$this->PrefixVRF = $PrefixVRF;
	}

	/**
	 * @return int
	 */
	public function getParentID(): int {
		return $this->ParentID;
	}

	/**
	 * @param int $ParentID
	 */
	public function setParentID(int $ParentID) {
		$this->ParentID = $ParentID;
	}

	/**
	 * @return int
	 */
	public function getMasterVRF(): int {
		return $this->MasterVRF;
	}

	/**
	 * @param int $MasterVRF
	 */
	public function setMasterVRF(int $MasterVRF) {
		$this->MasterVRF = $MasterVRF;
	}

}