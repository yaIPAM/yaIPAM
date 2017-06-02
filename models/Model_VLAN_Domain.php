<?php
// @TODO Move this to an entity
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
	private $DomainDescription = "";

	/**
	 * @return array
	 */
	static public function listDomains(): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$domains = $queryBuilder
			->select('d.domain_name', 'd.domain_id', 'd.domain_description')
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
			->select('d.domain_name', 'd.domain_id', 'd.domain_description')
			->from('vlan_domains', 'd')
			->setMaxResults(1)
			->orderBy('d.domain_name')
			->execute()
			->fetch();

		$this->setDomainID((int)$domains['domain_id']);
		$this->setDomainDescription((string)$domains['domain_description']);
		$this->setDomainName((string)$domains['domain_name']);

		return (array)$domains;
	}

	/**
	 * @param int $ID
	 * @return array
	 */
	public function selectByID(int $ID): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$domain = $queryBuilder
			->select('d.domain_name', 'd.domain_id', 'd.domain_description')
			->from('vlan_domains', 'd')
			->where('d.domain_id = ?')
			->setParameter(0, $ID)
			->execute()
			->fetch();

		$this->setDomainID((int)$domain['domain_id']);
		$this->setDomainDescription((string)$domain['domain_description']);
		$this->setDomainName((string)$domain['domain_name']);

		return (array)$domain;
	}

	/**
	 * @return bool
	 */
	public function save(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		if ($this->getDomainID() > 0) {
			$update = $queryBuilder
				->update('vlan_domains')
				->set('domain_name', '?')
				->set('domain_description', '?')
				->where('domain_id = ?')
				->setParameter(0, $this->getDomainName())
				->setParameter(1, $this->getDomainDescription())
				->setParameter(2, $this->getDomainID());

			if ($update->execute()) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			$insert = $queryBuilder
				->insert('vlan_domains')
				->setValue('domain_name', '?')
				->setValue('domain_description', '?')
				->setParameter(0, $this->getDomainName())
				->setParameter(1, $this->getDomainDescription());

			if ($insert->execute()) {
				$this->setDomainID($dbal->lastInsertId());
				return true;
			}
			else {
				return false;
			}
		}

	}

	/**
	 * @return bool
	 */
	public function delete(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();
		$delete = $queryBuilder
			->delete('vlan_domains')
			->where('domain_id = ?')
			->setParameter(0, $this->getDomainID());

		if ($delete->execute()) {
			return true;
		}
		else {
			return false;
		}

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

	/**
	 * @return string
	 */
	public function getDomainDescription(): string {
		return $this->DomainDescription;
	}

	/**
	 * @param string $DomainDescription
	 */
	public function setDomainDescription(string $DomainDescription) {
		$this->DomainDescription = $DomainDescription;
	}


}