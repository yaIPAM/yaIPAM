<?php
namespace Service;

use Symfony\Component\Yaml\Exception\RuntimeException;

#require_once SCRIPT_BASE .'/models/Model_Address.php';

/**
 * Model_Subnet.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 13:24
 */
class Prefixes {

    protected $em;
    protected $entity;

    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\Prefixes();
    }

	public function save(): bool {

			$this->em->beginTransaction();

			if ($this->getEntity()->getPrefix() == 0) {
                $this->getEntity()->setParentID(self::CalculateParentID($this->getEntity()->getPrefix() . "/" . $this->getEntity()->getPrefixlength(), $this->getEntity()->getMastervrf()));
            }

			try {
			    $this->em->persist($this->getEntity());
			    $this->em->flush();
            }
            catch (Exception $e) {
			    $this->em->rollBack();
			    return false;
            }

			if ($this->getEntity()->getPrefixid() == 0) {
                $this->RecalcTree($this->getEntity()->getParentID(), $this->getEntity()->getPrefixID());
               \Service\Addresses::calcNewPrefix($this->getEntity()->getParentID(), $this->getEntity()->getMasterVRF(), $this->getEntity()->getAFI());
            }
			$this->em->commit();
			return true;
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
				->setParameter('PrefixID', $this->getEntity()->getPrefixID());

            if ($delete->execute() === false) {
                $dbal->rollBack();
                return false;
            }

            if (\Service\Addresses::deleteByPrefix($this->getEntity()->getPrefixID()) === false) {
                $dbal->rollBack();
                return false;
            }


		} else if ($Option == 2) {
			$delete = $queryBuilder
				->delete('prefixes')
				->where('PrefixID = :PrefixID')
				->setParameter('PrefixID', $this->getEntity()->getPrefixID());

            if ($delete->execute() === false) {
                $dbal->rollBack();
                return false;
            }
		}

		if ($Option == 2) {
			$this->RecalcTree($this->getEntity()->getPrefixID());

			if (\Service\Addresses::calcNewPrefix($this->getEntity()->getPrefixID(), $this->getEntity()->getMasterVRF(), $this->getEntity()->getAFI()) === false) {
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
        global $EntityManager;

	    $queryBuilder = $EntityManager->createQueryBuilder();

	    $IP = \IPLib\Range\Subnet::fromString($Prefix);

	    $Prefix = explode("/", $Prefix);

	    try {
            $select = $queryBuilder
                ->select('COUNT(p.prefix) AS total')
                ->from('Entity\Prefixes', 'p')
                ->where('p.prefix = :Prefix')
                ->andWhere('p.prefixlength = :PrefixLength')
                ->andWhere('p.mastervrf = :VRF')
                ->setParameter('Prefix', ($IP->getAddressType() == 4) ? ip2long($Prefix[0]) : ip2long6($Prefix[0]))
                ->setParameter('PrefixLength', $Prefix[1])
                ->setParameter('VRF', $VRF)
                ->getQuery()
                ->getSingleResult();

            if ($select['total'] > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        catch (Exception $e) {
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

		$this->entity = $this->em->find('Entity\Prefixes', $ID);

		if ($this->entity == null) {
		    return false;
        }
        else {
		    return true;
        }

	}

	/**
	 * @param int $ParentID
	 * @return array
	 */
	public static function getSubPrefixes(int $ParentID): array {
		global $EntityManager;

		$queryBuilder = $EntityManager->createQueryBuilder();
		$select = $queryBuilder
			->select('p.prefixid, p.prefix, p.prefixlength, d.domainId, d.domainName, v.vlanname, v.vlanid, p.prefixdescription, p.afi, p.parentid')
			->from('Entity\Prefixes', 'p')
            ->leftJoin('Entity\Vlans', 'v', 'WITH', 'v.id = p.prefixvlan')
            ->leftjoin('Entity\VlanDomains', 'd', 'WITH', 'd.domainId = v.vlandomain')
			->where('p.parentid = :PrefixID')
            ->orderBy('p.prefix')
			->setParameter('PrefixID', $ParentID)
			->getQuery();

		$select = $select->getArrayResult();

		$subnets = array();
		$n = 1;
		$freesubnet_from = null;
		$freesubnet_to = null;
        $len = count($select);
        $usedaddresses = array();

        $ParentPrefix = $EntityManager->find('Entity\Prefixes', $ParentID);
        $ParentPrefix = \IPBlock::create($ParentPrefix->getPrefix().'/'.$ParentPrefix->getPrefixlength());

		foreach ($select as $data) {

		    $PrefixResource = stream_get_contents($data['prefix']);
		    $Prefix = \IPBlock::create($PrefixResource.'/'.$data['prefixlength']);

		    if ($n == 1 && $ParentPrefix->getFirstIp() == $Prefix->current()) {
                $subnets[] = array(
                    "prefix"    =>  $Prefix->current(),
                    "prefixlength"  =>  $data['prefixlength'],
                    "prefixdescription" =>  $data['prefixdescription'],
                    "prefixid"  =>  $data['prefixid'],
                    "vlanid"    =>  $data['vlanid'],
                    "domainName"    =>  $data['domainName'],
                    "vlanname"  =>  $data['vlanname'],
                    "free"  =>  false,

                );

                $NextFree = $Prefix->plus(1);
            }
            else if ($n == 1 && $ParentPrefix->getFirstIp() != $Prefix->current()) {
                $LastFree = $Prefix->minus(1);
                $FreeNetworks = \IPTools\Range::parse($ParentPrefix->getFirstIp().'-'.$LastFree->getLastIp())->getNetworks();
		        foreach ($FreeNetworks as $network) {
		            $Freenetwork = explode("/",$network);
                    $subnets[] = array(
                        "prefix"    =>  $Freenetwork[0],
                        "prefixlength"  =>  $Freenetwork[1],
                        "free"  =>  true,
                    );
                }

                $subnets[] = array(
                    "prefix"    =>  $Prefix->current(),
                    "prefixlength"  =>  $data['prefixlength'],
                    "prefixdescription" =>  $data['prefixdescription'],
                    "prefixid"  =>  $data['prefixid'],
                    "vlanid"    =>  $data['vlanid'],
                    "domainName"    =>  $data['domainName'],
                    "vlanname"  =>  $data['vlanname'],
                    "free"  =>  false,

                );

                $NextFree = $Prefix->plus(1);
            }

            if ($n > 1) {
		        try {
                    $LastFree = $Prefix->minus(1);
                    $FreeNetworks = \IPTools\Range::parse($NextFree->getFirstIp().'-'.$LastFree->getLastIp())->getNetworks();
                    foreach ($FreeNetworks as $network) {
                        $Freenetwork = explode("/",$network);
                        $subnets[] = array(
                            "prefix"    =>  $Freenetwork[0],
                            "prefixlength"  =>  $Freenetwork[1],
                            "free"  =>  true,
                        );
                    }
                }
                catch (\Exception $e) {}

                $subnets[] = array(
                    "prefix"    =>  $Prefix->current(),
                    "prefixlength"  =>  $data['prefixlength'],
                    "prefixdescription" =>  $data['prefixdescription'],
                    "prefixid"  =>  $data['prefixid'],
                    "vlanid"    =>  $data['vlanid'],
                    "domainName"    =>  $data['domainName'],
                    "vlanname"  =>  $data['vlanname'],
                    "free"  =>  false,

                );

		        try {
		            $NextFree = $Prefix->plus(1);
                }
                catch (\Exception $e) {

                }

            }

            if ($len == $n) {
		        try {
                    $FreeNetworks = \IPTools\Range::parse($NextFree->getFirstIp().'-'.$ParentPrefix->getLastIp())->getNetworks();
                    foreach ($FreeNetworks as $network) {
                        $Freenetwork = explode("/",$network);
                        $subnets[] = array(
                            "prefix"    =>  $Freenetwork[0],
                            "prefixlength"  =>  $Freenetwork[1],
                            "free"  =>  true,
                        );
                    }
                }
                catch (\Exception $e) {}
            }
            $n++;
        }

        #$subnets = array_orderby($subnets, 'prefix');

		return $subnets;
	}

    /**
     * @param string $PrefixName
     * @param int $VRF
     * @param int $PrefixID
     * @return int
     */
    public static function CalculateParentID(string $PrefixName, int $VRF, $PrefixID = 0): int {
		global $EntityManager;

		$Address = \IPLib\Range\Subnet::fromString($PrefixName);

		$queryBuilder = $EntityManager->createQueryBuilder();

		$select = $queryBuilder
			->select('p.prefixid', 'p.rangefrom')
			->from('Entity\Prefixes', 'p')
			->where('p.afi = :AFI')
			->andWhere('p.mastervrf = :VRF')
			->andWhere(':StartAddress >= p.rangefrom and :EndAddress <= p.rangeto')
			->orderBy('p.prefixlength', 'DESC')
            ->setMaxResults(1)
			->setParameter('AFI', $Address->getAddressType())
			->setParameter('StartAddress', $Address->getComparableStartString())
			->setParameter('EndAddress', $Address->getComparableEndString())
			->setParameter('VRF', $VRF);

		if ($PrefixID > 0) {
			$select->andWhere('p.prefixid != :PrefixID')->setParameter('PrefixID', $PrefixID);
		}
		$select = $select->getQuery()->getSingleResult();

		if ($select != null) {
			return $select['prefixid'];
		}
		else {
			return 0;
		}
	}

    /**
     * @return \Entity\Prefixes|null
     */
    public function getEntity()
    {
        return $this->entity;
    }


}