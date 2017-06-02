<?php
// @TODO Move this to an entity
/**
 * Model_OTV.php
 * Project: yaipam
 * User: ktammling
 * Date: 13.04.17
 * Time: 13:48
 */
class Model_OTV {

	private $OTVID = 0;
	private $OTVName = "";
	private $OTVDescription = "";
	private $OTVDomains = array();


	/**
	 * @param int $ID
	 * @return array
	 */
	public function selectByID(int $ID) {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$otv = $queryBuilder
			->select('o.OTVName', 'o.OTVID', 'd.DomainID', 'do.domain_name as DomainName', 'o.OTVDescription')
			->from('otv','o')
			->leftJoin('o','otv_domains', 'd', 'o.OTVID = d.OTVID')
			->leftJoin('d', 'vlan_domains', 'do', 'd.DomainID = do.domain_id')
			->where('o.OTVID = ?')
			->setParameter(0, $ID)
			->orderBy('do.domain_name', 'ASC')
			->execute()
			->fetchAll();

		#die(print_r($otv));
		$this->setOTVID($otv[0]['OTVID']);
		$this->setOTVName($otv[0]['OTVName']);
		$this->setOTVDescription($otv[0]['OTVDescription']);

		$domains = array();
		foreach ($otv as $data) {
			$domains[] = $data['DomainID'];
		}

		$this->setOTVDomains($domains);

		return $otv;
	}

	/**
	 * @return array
	 */
	public static function getAll(): array {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$otv = $queryBuilder
			->select('o.OTVName', 'o.OTVID', 'o.OTVDescription')
			->from('otv','o')
			->orderBy('o.OTVName', 'ASC')
			->execute()
			->fetchAll();

		return $otv;
	}

	/**
	 * @return bool
	 */
	public function save(): bool {
		global $dbal;

		$dbal->beginTransaction();

		$queryBuilder = $dbal->createQueryBuilder();

		if ($this->getOTVID() > 0) {
			$update = $queryBuilder
				->update('otv')
				->set('OTVName', '?')
				->set('OTVDescription', '?')
				->where('OTVID = ?')
				->setParameter(0, $this->getOTVName())
				->setParameter(1, $this->getOTVDescription())
				->setParameter(2, $this->getOTVID());

			if ($update->execute() === false) {
				$dbal->rollBack();
				return false;
			}


			$queryBuilder = $dbal->createQueryBuilder();
			$delete = $queryBuilder
				->delete('otv_domains')
				->where('OTVID = ?')
				->setParameter(0, $this->getOTVID());

			if ($delete->execute() === false) {
				$dbal->rollBack();
				return false;
			}

			$queryBuilder = $dbal->createQueryBuilder();
			$Domains = $this->getOTVDomains();
			foreach ($Domains as $key => $value) {
				$insert = $queryBuilder
					->insert('otv_domains')
					->setValue('OTVID', '?')
					->setValue('DomainID', '?')
					->setParameter(0, $this->getOTVID())
					->setParameter(1, $value);

				if ($insert->execute() === false) {
					$dbal->rollBack();
					return false;
				}
			}

		} else  {
			$insert = $queryBuilder
				->insert('otv')
				->setValue('OTVName', '?')
				->setValue('OTVDescription', '?')
				->setParameter(0, $this->getOTVName())
				->setParameter(1, $this->getOTVDescription());

			if (!$insert->execute()) {
				$dbal->rollBack();
				return false;
			}

			$this->setOTVID($dbal->lastInsertId());

			$queryBuilder = $dbal->createQueryBuilder();

			$Domains = $this->getOTVDomains();
			foreach ($Domains as $key => $value) {
				$insert = $queryBuilder
					->insert('otv_domains')
					->setValue('OTVID', '?')
					->setValue('DomainID', '?')
					->setParameter(0, $this->getOTVID())
					->setParameter(1, $value);

				if (!$insert->execute()) {
					$dbal->rollBack();
					return false;
				}
			}

		}

		$dbal->commit();

		return  true;

	}

	/**
	 * @return bool
	 */
	public function delete(): bool {
		global $dbal;

		$queryBuilder = $dbal->createQueryBuilder();

		$dbal->beginTransaction();

		$delete = $queryBuilder
			->delete('otv')
			->where('OTVID = ?')
			->setParameter(0, $this->getOTVID());

		if ($delete->execute() === false) {
			$dbal->rollBack();
			return false;
		}

		$delete = $queryBuilder
			->delete('otv_domains')
			->where('OTVID = ?')
			->setParameter(0, $this->getOTVID());

		if ($delete->execute() === false) {
			$dbal->rollBack();
			return false;
		}

		$update = $queryBuilder
			->update('vlans')
			->set('OTVDomain', 0)
			->where('OTVDomain = ?')
			->setParameter(0, $this->getOTVID());

		if ($update->execute() === false) {
			$dbal->rollBack();
			return false;
		}

		$dbal->commit();

		return true;
	}

	/**
	 * @return int
	 */
	public function getOTVID(): int {
		return $this->OTVID;
	}

	/**
	 * @param int $OTVID
	 */
	public function setOTVID(int $OTVID) {
		$this->OTVID = $OTVID;
	}

	/**
	 * @return string
	 */
	public function getOTVName(): string {
		return $this->OTVName;
	}

	/**
	 * @param string $OTVName
	 */
	public function setOTVName(string $OTVName) {
		$this->OTVName = $OTVName;
	}

	/**
	 * @return array
	 */
	public function getOTVDomains(): array {
		return $this->OTVDomains;
	}

	/**
	 * @param array $OTVDomains
	 */
	public function setOTVDomains(array $OTVDomains) {
		$this->OTVDomains = $OTVDomains;
	}

	/**
	 * @return string
	 */
	public function getOTVDescription(): string {
		return $this->OTVDescription;
	}

	/**
	 * @param string $OTVDescription
	 */
	public function setOTVDescription(string $OTVDescription) {
		$this->OTVDescription = $OTVDescription;
	}


}