<?php

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

/**
 * Model_VLAN.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 12:55
 */
class Model_VLAN {

	private $ID = 0;
	private $VlanID = 0;
	private $VlanName = "";
	private $VlanDomainID = 0;
	private $VlanDomainName = "";
	private $VlanOTVDomain = 0;


	/**
	 * @param int $id
	 * @return mixed
	 */
	public function get(int $id) {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$vlan = $queryBuilder
			->select('v.ID', 'v.VlanID', 'v.VlanName', 'd.domain_name', 'd.domain_id', 'v.OTVDomain')
			->from('vlans', 'v')
			->innerJoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
			->where('v.ID = ?')
			->setParameter(0, $id)
			->execute()
			->fetch();

		if (empty($vlan)) {
			return false;
		}
		else {
			$this->setID($vlan['ID']);
			$this->setVlanID($vlan['VlanID']);
			$this->setVlanName($vlan['VlanName']);
			$this->setVlanDomainID($vlan['domain_id']);
			$this->setVlanDomainName($vlan['domain_name']);
			$this->setVlanOTVDomain($vlan['OTVDomain']);
			return $vlan;
		}
	}

	/**
	 * @param int $DomainID
	 * @return array
	 */
	public function getAllByDomain(int $DomainID): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$vlan = $queryBuilder
			->select('v.ID', 'v.VlanID', 'v.VlanName')
			->from('vlans', 'v')
			->where('v.VlanDomain = ?')
			->setParameter(0, $DomainID)
			->orderBy("v.VlanID")
			->execute()
			->fetchAll();

		$queryBuilder = $dbal->createQueryBuilder();
		$vlanDomain = $queryBuilder
			->select('o.OTVID', 'o2.OTVName')
			->from('vlan_domains', 'd')
			->innerJoin('d','otv_domains', 'o', 'o.DomainID = d.domain_id')
			->innerjoin('o', 'otv', 'o2', 'o2.OTVID = o.OTVID')
			->where('d.domain_id = ?')
			->setParameter(0, $DomainID)
			->execute()
			->fetchAll();

		print_r($vlanDomain);

		$otvVlanArray = array();
		foreach ($vlanDomain as $data) {
			$queryBuilder = $dbal->createQueryBuilder();
			$otvVlans = $queryBuilder
				->select('v.ID', 'v.VlanID', 'v.VlanName', 'd.domain_name', '"true" AS OTVVlan', ':VlanDomain as VlanDomain', ':Overlay AS Overlay')
				->from('vlans', 'v')
				->innerJoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
				->where('v.OTVDomain = :OTVID AND v.VlanDomain != :VlanDomain')
				->setParameter('OTVID', $data['OTVID'])
				->setParameter('VlanDomain', $DomainID)
				->setParameter('Overlay', $data['OTVName'])
				->execute()
				->fetchAll();


				$vlan = array_merge($vlan, $otvVlans);
		}

		$new_vlans = array_orderby($vlan, 'VlanID');


			return $new_vlans;
	}

	/**
	 * @param int $VLANID
	 * @return mixed
	 */
	public function findByVLANID(int $VLANID, int $Domain = 0) {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		if ($Domain > 0) {
			$vlan = $queryBuilder
				->select('v.ID', 'v.VlanID', 'v.VlanName', 'd.domain_name', 'd.domain_id')
				->from('vlans', 'v')
				->innerJoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
				->where('v.VlanID = ?')
				->andWhere('v.VlanDomain = ?')
				->setParameter(0, $VLANID)
				->setParameter(1, $Domain)
				->execute()
				->fetch();
		}
		else {
			$vlan = $queryBuilder
				->select('v.ID', 'v.VlanID', 'v.VlanName', 'd.domain_name', 'd.domain_id')
				->from('vlans', 'v')
				->innerJoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
				->where('v.VlanID = ?')
				->setParameter(0, $VLANID)
				->execute()
				->fetch();
		}
		if (empty($vlan)) {
			return false;
		}
		else {
			$this->setID($vlan['ID']);
			$this->setVlanID($vlan['VlanID']);
			$this->setVlanName($vlan['VlanName']);
			$this->setVlanDomainID($vlan['domain_id']);
			$this->setVlanDomainName($vlan['domain_name']);
			return $vlan;
		}
	}

	/**
	 * @param int $DomainID
	 * @return mixed
	 */
	public function firstVLANByDomain(int $DomainID) {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$vlan = $queryBuilder
			->select('v.VlanID', 'v.VlanName')
			->from('vlans', 'v')
			->where('v.VlanDomain = ?')
			->setParameter(0, $DomainID)
			->orderBy("v.VlanID", 'ASC')
			->setMaxResults(1)
			->execute()
			->fetch();

			return $vlan;
	}

	/**
	 * @param int $DomainID
	 * @return mixed
	 */
	public function LastVLANByDomain(int $DomainID) {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$vlan = $queryBuilder
			->select('v.VlanID', 'v.VlanName')
			->from('vlans', 'v')
			->where('v.VlanDomain = ?')
			->setParameter(0, $DomainID)
			->orderBy("v.VlanID", 'DESC')
			->setMaxResults(1)
			->execute()
			->fetch();

			return $vlan;
	}

	/**
	 * @return bool
	 */
	public function create(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$vlan = $queryBuilder
			->insert('vlans')
			->setValue('VlanID', ':VlanID')
			->setValue('VlanName', ':VlanName')
			->setValue('VlanDomain', ':VlanDomain')
			->setValue('OTVDomain', ':OTVDomain')
			->setParameter('VlanID', $this->getVlanID())
			->setParameter('VlanName', $this->getVlanName())
			->setParameter('VlanDomain', $this->getVlanDomainID())
			->setParameter('OTVDomain', $this->getVlanOTVDomain());

		if ($vlan->execute()) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function save(): bool {
		global $dbal;
		
		$queryBuilder = $dbal->createQueryBuilder();
		$vlan = $queryBuilder
			->update('vlans')
			->set('VlanName', '?')
			->set('VlanDomain', '?')
			->set('OTVDomain', '?')
			->where('ID = ?')
			->setParameter(0, $this->getVlanName())
			->setParameter(1, $this->getVlanDomainID())
			->setParameter(3, $this->getID())
			->setParameter(2, $this->getVlanOTVDomain());

		try {
			$vlan->execute();
			return true;
		}
		catch (\Exception $e) {
			return false;
		}
	}

	public function delete(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$vlan = $queryBuilder
			->delete('vlans')
			->where('ID = ?')
			->setParameter(0, $this->getID());

		try {
			$vlan->execute();
			return true;
		}
		catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @param int $DomainID
	 * @return bool
	 */
	public static function DeleteAllByDomain(int $DomainID): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$delete = $queryBuilder
			->delete('vlans')
			->where('VlanDomain = ?')
			->setParameter(0, $DomainID);

		if ($delete->execute()) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * @param int $DomainID
	 * @return int
	 */
	public static function CountAllByDomain(int $DomainID): int {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$numrows = $queryBuilder
			->select('COUNT(*) AS total')
			->from('vlans')
			->where('VlanDomain = ?')
			->setParameter(0, $DomainID)
			->execute()
			->fetch();

		return $numrows['total'];

	}

	/**
	 * @return int
	 */
	public function getID(): int {
		return $this->ID;
	}

	/**
	 * @param int $ID
	 */
	public function setID(int $ID) {
		$this->ID = $ID;
	}

	/**
	 * @return int
	 */
	public function getVlanID(): int {
		return $this->VlanID;
	}

	/**
	 * @param int $VlanID
	 */
	public function setVlanID(int $VlanID) {
		$this->VlanID = $VlanID;
	}

	/**
	 * @return string
	 */
	public function getVlanName(): string {
		return $this->VlanName;
	}

	/**
	 * @param string $VlanName
	 */
	public function setVlanName(string $VlanName) {
		$this->VlanName = $VlanName;
	}

	/**
	 * @return int
	 */
	public function getVlanDomainID(): int {
		return $this->VlanDomainID;
	}

	/**
	 * @param int $VlanDomainID
	 */
	public function setVlanDomainID(int $VlanDomainID) {
		$this->VlanDomainID = $VlanDomainID;
	}

	/**
	 * @return string
	 */
	public function getVlanDomainName(): string {
		return $this->VlanDomainName;
	}

	/**
	 * @param string $VlanDomainName
	 */
	public function setVlanDomainName(string $VlanDomainName) {
		$this->VlanDomainName = $VlanDomainName;
	}

	/**
	 * @return int
	 */
	public function getVlanOTVDomain(): int {
		return $this->VlanOTVDomain;
	}

	/**
	 * @param int $VlanOTVDomain
	 */
	public function setVlanOTVDomain(int $VlanOTVDomain) {
		$this->VlanOTVDomain = $VlanOTVDomain;
	}


}