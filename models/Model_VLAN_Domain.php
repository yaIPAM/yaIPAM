<?php

/**
 * Model_VLAN_Domainphp
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 16:02
 */
class Model_VLAN_Domain {

	private $DomainID = 0;
	private $DomainName = "";

	/**
	 * @return array
	 */
	static public function listDomains(): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$domains = $queryBuilder
			->select('d.domain_name', 'd.domain_id')
			->from('vlan_domains', 'd')
			->orderBy('d.domain_name')
			->execute()
			->fetchAll();

		return $domains;
	}

	/**
	 * @return array
	 */
	public function selectFirst(): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$domains = $queryBuilder
			->select('d.domain_name', 'd.domain_id')
			->from('vlan_domains', 'd')
			->setMaxResults(1)
			->orderBy('d.domain_name')
			->execute()
			->fetch();

		return $domains;
	}

	/**
	 * @return int
	 */
	public function getDomainID(): int {
		return $this->DomainID;
	}

	/**
	 * @param int $DomainID
	 */
	public function setDomainID(int $DomainID) {
		$this->DomainID = $DomainID;
	}

	/**
	 * @return string
	 */
	public function getDomainName(): string {
		return $this->DomainName;
	}

	/**
	 * @param string $DomainName
	 */
	public function setDomainName(string $DomainName) {
		$this->DomainName = $DomainName;
	}


}