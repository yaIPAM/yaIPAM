<?php
// @TODO Move this to an entity
require_once SCRIPT_BASE .'/models/Model_Address.php';

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
	private $PrefixState = 0;
	private $PrefixVLAN = 0;

	public function save(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		if ($this->getPrefixID() == 0) {

			$dbal->beginTransaction();

			$this->setParentID(\Service\Prefixes::CalculateParentID($this->getPrefix()."/".$this->getPrefixLength(), $this->getMasterVRF()));

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
				->setValue('PrefixState', ':PrefixState')
                ->setValue('PrefixVLAN', ':PrefixVLAN')
				->setParameter('PrefixLength', $this->getPrefixLength())
				->setParameter('AFI', $this->getAFI())
				->setParameter('PrefixDescription', $this->getPrefixDescription())
				->setParameter('RangeFrom', $this->getRangeFrom())
				->setParameter('RangeTo', $this->getRangeTo())
				->setParameter('MasterVRF', $this->getMasterVRF())
				->setParameter('ParentID', $this->getParentID())
				->setParameter('PrefixState', $this->getPrefixState())
                ->setParameter('PrefixVLAN', $this->getPrefixVLAN());

			if ($this->getAFI() == 4) {
				$insert->setParameter('Prefix', ip2long($this->getPrefix()));

			}
			else if ($this->getAFI() == 6) {
				$insert->setParameter('Prefix', ip2long6($this->getPrefix()));
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

			$this->RecalcTree($this->getParentID(), $this->getPrefixID());
            Model_Address::calcNewPrefix($this->getParentID(), $this->getMasterVRF(), $this->getAFI());
			$dbal->commit();
			return true;
		}
		else if ($this->getPrefixID() > 0) {
			$dbal->beginTransaction();

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
				->set('PrefixState', ':PrefixState')
                ->set('PrefixVLAN', ':PrefixVLAN')
				->where('PrefixID = :PrefixID')
				->setParameter('PrefixLength', $this->getPrefixLength())
				->setParameter('AFI', $this->getAFI())
				->setParameter('PrefixDescription', $this->getPrefixDescription())
				->setParameter('RangeFrom', $this->getRangeFrom())
				->setParameter('RangeTo', $this->getRangeTo())
				->setParameter('PrefixID', $this->getPrefixID())
				->setParameter('MasterVRF', $this->getMasterVRF())
				->setParameter('ParentID', $this->getParentID())
				->setParameter('PrefixState', $this->getPrefixState())
                ->setParameter('PrefixVLAN', $this->getPrefixVLAN());

			if ($this->getAFI() == 4) {
				$update->setParameter('Prefix', ip2long($this->getPrefix()));

			}
			else if ($this->getAFI() == 6) {
				$update->setParameter('Prefix', ip2long6($this->getPrefix()));
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

	public function delete(int $Option = 1): bool {
		global $dbal;

		$dbal->beginTransaction();

		$queryBuilder = $dbal->createQueryBuilder();

		if ($Option == 1) {
			$delete = $queryBuilder
				->delete('prefixes')
				->where('PrefixID = :PrefixID')
				->orWhere('ParentID = :PrefixID')
				->setParameter('PrefixID', $this->getPrefixID());

            if ($delete->execute() === false) {
                $dbal->rollBack();
                return false;
            }

            if (Model_Address::deleteByPrefix($this->getPrefixID()) === false) {
                $dbal->rollBack();
                return false;
            }


		} else if ($Option == 2) {
			$delete = $queryBuilder
				->delete('prefixes')
				->where('PrefixID = :PrefixID')
				->setParameter('PrefixID', $this->getPrefixID());

            if ($delete->execute() === false) {
                $dbal->rollBack();
                return false;
            }
		}

		if ($Option == 2) {
			$this->RecalcTree($this->getPrefixID());

			if (Model_Address::calcNewPrefix($this->getPrefixID(), $this->getMasterVRF(), $this->getAFI()) === false) {
			    $dbal->rollBack();
			    return false;
            }
		}

		$dbal->commit();

		return true;
	}

    /**
     * @param string $Prefix
     * @param int $VRF
     * @return bool
     */
    public static function PrefixExists(string $Prefix, int $VRF): bool {
	    global $dbal;

	    $queryBuilder = $dbal->createQueryBuilder();

	    $IP = \IPLib\Range\Subnet::fromString($Prefix);

	    $Prefix = explode("/", $Prefix);

	    $select = $queryBuilder
            ->select('COUNT(*) AS total')
            ->from('prefixes')
            ->where('Prefix = :Prefix')
            ->andWhere('PrefixLength = :PrefixLength')
            ->andWhere('MasterVRF = :VRF')
            ->setParameter('Prefix', ($IP->getAddressType() == 4) ? ip2long($Prefix[0]) : ip2long6($Prefix[0]))
            ->setParameter('PrefixLength', $Prefix[1])
            ->setParameter('VRF', $VRF)
            ->execute()
            ->fetch();

	    if ($select['total'] > 0) {
	        return true;
        }
        else {
	        return false;
        }
    }

	/**
	 * @param int $ParentID
	 * @param int $SelfID
	 * @return bool
	 */
	public function RecalcTree(int $ParentID, int $SelfID = 0): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$select = $queryBuilder
			->select('PrefixID', 'MasterVRF', 'Prefix', 'AFI', 'PrefixLength')
			->from('prefixes')
			->where('ParentID = :ParentID')
			->andWhere('ParentID <> 0')
			->setParameter('ParentID', $ParentID);

		if ($SelfID != 0) {
			$select->andWhere('PrefixID <> :SelfID')
			->setParameter('SelfID', $SelfID);
		}

		$select = $select->execute()->fetchAll();

		foreach ($select as $data) {
			$Prefix = ($data['AFI']==4) ? long2ip($data['Prefix']) : long2ip6($data['Prefix']);
			$Prefix = $Prefix."/".$data['PrefixLength'];
			$NewParent = self::CalculateParentID($Prefix, $data['MasterVRF'], $data['PrefixID']);
			echo $NewParent;
			$queryBuilder = $dbal->createQueryBuilder();
			$queryBuilder
				->update('prefixes')
				->set('ParentID', ':ParentID')
				->where('PrefixID = :PrefixID')
				->setParameter('ParentID', $NewParent)
				->setParameter('PrefixID', $data['PrefixID']);

			if ($queryBuilder->execute() === false) {
				return false;
			}
		}

		return true;
	}

	public static function createSubnetBreadcrumbs(int $PrefixID): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$select = $queryBuilder
			->select('PrefixID', 'ParentID', 'Prefix', 'PrefixLength', 'AFI', 'MasterVRF')
			->from('prefixes')
			->where('PrefixID = :PrefixID')
			->setParameter('PrefixID', $PrefixID)
			->execute()
			->fetch();

		$breadcrumbs = array();
		$breadcrumbs['Prefixes'][] = array(
			"PrefixID"  =>  $select['PrefixID'],
			"ParentID"  =>  $select['ParentID'],
			"Prefix"    =>  ($select['AFI'] == 4) ? long2ip($select['Prefix']) : long2ip6($select['Prefix']),
			"PrefixLength"  =>  $select['PrefixLength'],
		);

		while ($select['ParentID'] > 0) {
			$queryBuilder = $dbal->createQueryBuilder();
			$select = $queryBuilder
				->select('PrefixID', 'ParentID', 'Prefix', 'PrefixLength', 'AFI', 'MasterVRF')
				->from('prefixes')
				->where('PrefixID = :PrefixID')
				->setParameter('PrefixID', $select['ParentID'])
				->execute()
				->fetch();

			$breadcrumbs['Prefixes'][] = array(
				"PrefixID"  =>  $select['PrefixID'],
				"ParentID"  =>  $select['ParentID'],
				"Prefix"    =>  ($select['AFI'] == 4) ? long2ip($select['Prefix']) : long2ip6($select['Prefix']),
				"PrefixLength"  =>  $select['PrefixLength'],
			);
		}

		$breadcrumbs['Prefixes'] = array_reverse($breadcrumbs['Prefixes']);

		$queryBuilder = $dbal->createQueryBuilder();
		$select = $queryBuilder
			->select('VRFID', 'VRFName')
			->from('vrfs')
			->where('VRFID = :VRFID')
			->setParameter('VRFID', $select['MasterVRF'])
			->execute()
			->fetch();

		$breadcrumbs['vrf'] = array(
			"VRFID" =>  $select['VRFID'],
			"VRFName"   =>  $select['VRFName'],
		);

		return $breadcrumbs;

	}

	/**
	 * @param int $ID
	 * @return bool
	 */
	public function getByID(int $ID): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$select = $queryBuilder
			->select('*')
			->from('prefixes')
			->where('PrefixID = :PrefixID')
			->setParameter('PrefixID', $ID)
			->execute()
			->fetch();

		if (empty($select)) {
			return false;
		}
		else {
			$this->setMasterVRF($select['MasterVRF']);
			$this->setPrefixID($select['PrefixID']);
			$this->setAFI($select['AFI']);
			if ($this->getAFI() == 4) {
				$this->setPrefix(long2ip($select['Prefix']));
			} else if ($this->getAFI() == 6) {
				$this->setPrefix(long2ip6($select['Prefix']));
			}
			$this->setPrefixLength($select['PrefixLength']);
			$this->setRangeTo($select['RangeTo']);
			$this->setRangeFrom($select['RangeFrom']);
			$this->setPrefixDescription($select['PrefixDescription']);
			$this->setParentID($select['ParentID']);
			$this->setPrefixState($select['PrefixState']);
			$this->setPrefixVLAN($select['PrefixVLAN']);

			return true;
		}
	}

	/**
	 * @param int $ParentID
	 * @return array
	 */
	public static function getSubPrefixes(int $ParentID): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$select = $queryBuilder
			->select('*')
			->from('prefixes', 'p')
            ->leftJoin('p', 'vlans', 'v', 'v.ID = p.PrefixVLAN')
            ->leftjoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
			->where('ParentID = :PrefixID')
			->setParameter('PrefixID', $ParentID)
			->execute()
			->fetchAll();

		return $select;
	}

	public static function CalculateParentID(string $PrefixName, int $VRF, $PrefixID = 0): int {
		global $dbal;

		$Address = \IPLib\Range\Subnet::fromString($PrefixName);

		$queryBuilder = $dbal->createQueryBuilder();
		$select = $queryBuilder
			->select('PrefixID')
			->from('prefixes')
			->where('AFI = :AFI')
			->andWhere('MasterVRF = :VRF')
			->andWhere(':StartAddress >= RangeFrom and :EndAddress <= RangeTo')
			->orderBy('Prefix', 'DESC')
			->setParameter('AFI', $Address->getAddressType())
			->setParameter('StartAddress', $Address->getComparableStartString())
			->setParameter('EndAddress', $Address->getComparableEndString())
			->setParameter('VRF', $VRF);

		if ($PrefixID > 0) {
			$select->andWhere('PrefixID != :PrefixID')->setParameter('PrefixID', $PrefixID);
		}

		$select = $select->execute()->fetch();

		if (!empty($select['PrefixID'])) {
			return $select['PrefixID'];
		}
		else {
			return 0;
		}
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

	/**
	 * @return int
	 */
	public function getPrefixState(): int {
		return $this->PrefixState;
	}

	/**
	 * @param int $PrefixState
	 */
	public function setPrefixState(int $PrefixState) {
		$this->PrefixState = $PrefixState;
	}

    /**
     * @return int
     */
    public function getPrefixVLAN(): int
    {
        return $this->PrefixVLAN;
    }

    /**
     * @param int $PrefixVLAN
     */
    public function setPrefixVLAN(int $PrefixVLAN)
    {
        $this->PrefixVLAN = $PrefixVLAN;
    }


}