<?php
namespace Service;

/**
 * Model_Subnet.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 13:24
 */
class Prefixes
{
    protected $em;
    protected $entity;

    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\Prefixes();
    }

    public function save(): bool
    {
        $this->em->beginTransaction();

        if ($this->getEntity()->getPrefix() == 0 && $this->getEntity()->getPrefixlength() != 0) {
            $this->getEntity()->setParentID(self::CalculateParentID($this->getEntity()->getPrefix()."/".$this->getEntity()->getPrefixlength(), $this->getEntity()->getMastervrf()));
        }

        try {
            $this->em->persist($this->getEntity());
            $this->em->flush();

        } catch (\Exception $e) {
            $this->em->rollBack();
            return false;
        }

        Addresses::calcNewPrefix($this->getEntity()->getParentID(), $this->getEntity()->getMasterVRF(), $this->getEntity()->getAFI());

        $this->RecalcTree($this->getEntity()->getParentID(), $this->getEntity()->getPrefixID());

        $this->em->commit();

        return true;
    }

    /**
     * @param int $VRFID
     * @return bool
     */
    public function deleteByVRF(int $VRFID): bool
    {
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

    private function deleteAll($PrefixID): bool
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $delete_prefixes = $queryBuilder
            ->select('p.prefixid')
            ->from('Entity\Prefixes', 'p')
            ->where('p.parentid = :PrefixID')
            ->orWhere('p.prefixid = :PrefixID')
            ->setParameter('PrefixID', $PrefixID)
            ->getQuery()
            ->getArrayResult();

        try {
            foreach ($delete_prefixes as $delete_prefix) {
                $DeleteEntity = $this->em->find('Entity\Prefixes', $PrefixID);
                $this->em->remove($DeleteEntity);
            }
            $this->em->flush();
        } catch (\Exception $e) {
            return false;
        }

        if (Addresses::deleteByPrefix($PrefixID) === false) {
            return false;
        }

        return true;
    }

    private function deleteRecalc($PrefixID, $MasterVRF, $AFI): bool
    {
        $DeleteEntity = $this->em->find('Entity\Prefixes', $PrefixID);
        try {
            $this->em->remove($DeleteEntity);
            $this->em->flush();
        } catch (\Exception $e) {
            return false;
        }

        $this->RecalcTree($PrefixID);

        if (Addresses::calcNewPrefix($PrefixID, $MasterVRF, $AFI) === false) {
            return false;
        }

        return true;
    }

    public function delete(int $Option = 1): bool
    {

        $this->em->beginTransaction();
        $queryBuilder = $this->em->createQueryBuilder();

        $PrefixID = $this->getEntity()->getPrefixID();
        $MasterVRF = $this->getEntity()->getMasterVRF();
        $AFI = $this->getEntity()->getAFI();

        if ($Option == 1) {
            if (!$this->deleteAll($PrefixID)) {
                $this->em->rollback();
                return false;
            }
        } elseif ($Option == 2) {
            if (!($this->deleteRecalc($PrefixID, $MasterVRF, $AFI))) {
                $this->em->rollback();
                return false;
            }
        }

        $this->em->commit();

        return true;
    }

    /**
     * @param $Prefix
     * @return bool
     */
    public static function PrefixAlreadylinked($Prefix): bool
    {
        global $EntityManager;

        $PrefixLength = $Prefix[1];
        $Prefix = explode("/", $Prefix[0]);
        $Prefix = ip2long6($Prefix);

        $EntityLinked = $EntityManager->getRepository('Entity\Prefixes')->findOneBy(['prefix' => $Prefix, 'prefixlength' => $PrefixLength]);

        if ($EntityLinked == null) {
            return false;
        }

        $EntityPrefix = $EntityManager->find('Entity\Prefixes', $EntityLinked->getPrefixid());

        if ($EntityPrefix != null) {
            return true;
        }

        return false;

    }

    /**
     * @param string $Prefix
     * @param int $VRF
     * @return bool
     */
    public static function PrefixExists(string $Prefix, int $VRF): bool
    {
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
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $ParentID
     * @param int $SelfID
     * @return bool
     */
    public function RecalcTree(int $ParentID, int $SelfID = 0): bool
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $select = $queryBuilder
            ->select('p.prefixid', 'p.mastervrf', 'p.prefix', 'p.afi', 'p.prefixlength')
            ->from('Entity\Prefixes', 'p')
            ->where('p.parentid = :ParentID')
            ->andWhere('p.parentid <> 0')
            ->setParameter('ParentID', $ParentID);

        if ($SelfID != 0) {
            $select->andWhere('p.prefixid <> :SelfID')
            ->setParameter('SelfID', $SelfID);
        }

        $select = $select->getQuery()->getArrayResult();

        foreach ($select as $data) {
            if ($data['prefixlength'] == 0) {
                continue;
            }
            $Prefix = stream_get_contents($data['prefix']);
            $Prefix = ($data['afi'] == 4) ? long2ip($Prefix) : long2ip6($Prefix);
            $Prefix = $Prefix."/".$data['prefixlength'];
            $NewParent = self::CalculateParentID($Prefix, $data['mastervrf'], $data['prefix']);
            $PrefixEntity = $this->em->find('Entity\Prefixes', $data['prefixid']);
            $PrefixEntity->setParentid($NewParent);
            try {
                $this->em->persist($PrefixEntity);
                $this->em->flush();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    public static function createSubnetBreadcrumbs(int $PrefixID): array
    {
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
    public function getByID(int $ID): bool
    {
        $this->entity = $this->em->find('Entity\Prefixes', $ID);

        if ($this->entity == null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param int $ParentID
     * @return array
     */
    public static function getSubPrefixes(int $ParentID): array
    {
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
        $len = count($select);

        $ParentPrefix = $EntityManager->find('Entity\Prefixes', $ParentID);
        $ParentPrefix = \IPBlock::create($ParentPrefix->getPrefix().'/'.$ParentPrefix->getPrefixlength());

        foreach ($select as $data) {
            $PrefixResource = stream_get_contents($data['prefix']);
            $Prefix = \IPBlock::create($PrefixResource.'/'.$data['prefixlength']);

            if ($n == 1 && $ParentPrefix->getFirstIp() == $Prefix->current()) {
                $subnets = self::parseNonFreeSubnet($subnets, $Prefix, $data);

                try {
                    $NextFree = $Prefix->plus(1);
                } catch (\Exception $e) {
                    // Do nothing if there is nothing to do
                }
            } elseif ($n == 1 && $ParentPrefix->getFirstIp() != $Prefix->current()) {
                $LastFree = $Prefix->minus(1);
                $subnets = self::parseFreeSubnet($subnets, $ParentPrefix->getFirstIp(), $LastFree->getLastIp());
                $subnets = self::parseNonFreeSubnet($subnets, $Prefix, $data);

                try {
                    $NextFree = $Prefix->plus(1);
                } catch (\Exception $e) {
                    $NextFree = $Prefix;
                }
            }

            if ($n > 1) {
                $LastFree = $Prefix->minus(1);
                $subnets = self::parseFreeSubnet($subnets, $NextFree->getFirstIp(), $LastFree->getLastIp());
                $subnets = self::parseNonFreeSubnet($subnets, $Prefix, $data);

                try {
                    $NextFree = $Prefix->plus(1);
                } catch (\Exception $e) {
                    // Do nothing if there is nothing to do
                }
            }

            if ($len == $n) {
                $subnets = self::parseFreeSubnet($subnets, $NextFree->getFirstIp(), $ParentPrefix->getLastIp());
            }
            $n++;
        }

        return $subnets;
    }

    public static function parseNonFreeSubnet($subnets, $Prefix, $data)
    {
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

        return $subnets;
    }

    public static function parseFreeSubnet($subnets, $FirstIP, $LastIP)
    {
        try {
            $FreeNetworks = \IPTools\Range::parse($FirstIP.'-'.$LastIP)->getNetworks();
            foreach ($FreeNetworks as $network) {
                $Freenetwork = explode("/", $network);
                $subnets[] = array(
                    "prefix"    =>  $Freenetwork[0],
                    "prefixlength"  =>  $Freenetwork[1],
                    "free"  =>  true,
                );
            }
        } catch (\Exception $e) {
            // Do nothing when nothing is to do
        }

        return $subnets;
    }

    /**
     * @param string $PrefixName
     * @param int $VRF
     * @param int $PrefixID
     * @return int
     */
    public static function CalculateParentID(string $PrefixName, int $VRF, $PrefixID = 0): int
    {
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
        } else {
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
