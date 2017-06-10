<?php
namespace Service;

/**
 * Model_VLAN.php
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 12:55
 */
class Vlans
{
    private $em;
    private $entity;


    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\Vlans();
    }

    /**
     * @return \Entity\Vlans
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function get(int $id)
    {
        $Vlan = $this->em->find('Entity\Vlans', $id);

        if ($Vlan == null) {
            return false;
        } else {
            $this->entity = $Vlan;

            return $Vlan;
        }
    }

    /**
     * @param int $DomainID
     * @return array
     */
    public function getAllByDomain(int $DomainID): array
    {
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
            ->innerJoin('d', 'otv_domains', 'o', 'o.DomainID = d.domain_id')
            ->innerjoin('o', 'otv', 'o2', 'o2.OTVID = o.OTVID')
            ->where('d.domain_id = ?')
            ->setParameter(0, $DomainID)
            ->execute()
            ->fetchAll();

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
     * @return array
     */
    public static function getAll(): array
    {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();
        $vlan = $queryBuilder
            ->select('v.ID', 'v.VlanID', 'v.VlanName', 'd.domain_name')
            ->from('vlans', 'v')
            ->innerJoin('v', 'vlan_domains', 'd', 'd.domain_id = v.VlanDomain')
            ->orderBy("d.domain_name", "ASC")
            ->addOrderBy('v.VlanID', 'ASC')
            ->execute()
            ->fetchAll();

        return $vlan;
    }

    /**
     * @param int $VLANID
     * @return mixed
     */
    public function findByVLANID(int $VLANID, int $Domain = null)
    {
        global $whoops;

        try {
            if ($Domain != null) {
                $Vlan = $this->em->getRepository('Entity\Vlans')->findOneByVlanid($VLANID);
            } else {
                $Vlan = $this->em->getRepository('Entity\Vlans')->findOneBy(array('vlanid' => $VLANID, 'vlandomain' => $Domain));
            }

            $this->entity = $Vlan;
            return $this;
        }
        catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    /**
     * @param int $DomainID
     * @return mixed
     */
    public function firstVLANByDomain(int $DomainID)
    {
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
    public function LastVLANByDomain(int $DomainID)
    {
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
    public function create(): bool
    {
        global $whoops;

        try {
            $this->em->persist($this->getEntity());
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        global $whoops;

        try {
            $this->em->persist($this->getEntity());
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    public function delete(): bool
    {
        global $whoops;

        try {
            $this->em->remove($this->getEntity());
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    /**
     * @param int $DomainID
     * @return bool
     */
    public static function DeleteAllByDomain(int $DomainID): bool
    {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();
        $delete = $queryBuilder
            ->delete('vlans')
            ->where('VlanDomain = ?')
            ->setParameter(0, $DomainID);

        if ($delete->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $DomainID
     * @return int
     */
    public static function CountAllByDomain(int $DomainID): int
    {
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
}
