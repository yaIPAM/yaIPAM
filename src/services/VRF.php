<?php

namespace Service;

use Symfony\Component\Yaml\Exception\RuntimeException;

/**
 * Model_VRF.php
 * Project: yaipam
 * User: ktammling
 * Date: 17.04.17
 * Time: 12:28
 */
class VRF
{
    protected $em;
    protected $entity;

    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\Vrfs();
    }


    public static function getAll(): array
    {
        global $EntityManager;

        return $EntityManager->getRepository('Entity\Vrfs')->findAll();
    }


    public function save(): bool
    {
	    global $whoops;

        if (\Doctrine\ORM\UnitOfWork::STATE_NEW === $this->em->getUnitOfWork()->getEntityState($this->entity)) {
            try {
                $this->em->getConnection()->beginTransaction();
                $this->em->persist($this->entity);
                $this->em->flush();
                $Prefix = new \Entity\Prefixes();
                $RootPrefix = \IPLib\Range\Subnet::fromString('0.0.0.0/0');
                $Prefix->setPrefix('0.0.0.0/0');
	            $Prefix->setPrefixlength(0);
                $Prefix->setPrefixdescription("IPv4 Root Prefix");
                $Prefix->setRangefrom($RootPrefix->getComparableStartString());
                $Prefix->setRangeto($RootPrefix->getComparableEndString());
                $Prefix->setMastervrf($this->getEntity()->getVrfid());
                $Prefix->setPrefixstate(STATE_ALLOCATED);
                $Prefix->setPrefixvlan(0);
                $Prefix->setParentid(0);
                $this->em->persist($Prefix);
                $Prefix = new \Entity\Prefixes();
                $RootPrefix = \IPLib\Range\Subnet::fromString('::/0');
                $Prefix->setPrefix('::/0');
	            $Prefix->setPrefixlength(0);
                $Prefix->setPrefixdescription("IPv6 Root Prefix");
                $Prefix->setRangefrom($RootPrefix->getComparableStartString());
                $Prefix->setRangeto($RootPrefix->getComparableEndString());
                $Prefix->setMastervrf($this->getEntity()->getVrfid());
                $Prefix->setPrefixstate(STATE_ALLOCATED);
                $Prefix->setPrefixvlan(0);
                $Prefix->setParentid(0);
                $this->em->persist($Prefix);

                $this->em->flush();
                $this->em->getConnection()->commit();

                return true;
            } catch (\Exception $e) {
	            $this->em->getConnection()->rollBack();
                $whoops->handleException($e);

                return false;
            }
        } elseif (\Doctrine\ORM\UnitOfWork::STATE_MANAGED === $this->em->getUnitOfWork()->getEntityState($this->entity)) {
            try {
                $this->em->persist($this->entity);
                $this->em->flush();

                return true;
            } catch (Exception $e) {
	            $whoops->handleException($e);

                return false;
            }
        }
    }

    /**
     * @param int $VRFID
     * @return object|bool
     */
    public function getByID(int $VRFID)
    {
        $this->entity = $this->em->find('Entity\Vrfs', $VRFID);

        if ($this->entity == null) {
            return false;
        } else {
            return $this->entity;
        }
    }

    public static function getWithRoot(): array
    {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('VRFName', 'VRFID', 'VRFDescription')
            ->from('vrfs')
            ->orderBy('VRFID')
            ->execute()
            ->fetchAll();

        $vrfs = array();
        foreach ($select as $data) {
            $queryBuilder = $dbal->createQueryBuilder();
            $IPv4Root = $queryBuilder
                ->select('PrefixID')
                ->from('prefixes')
                ->where('MasterVRF = :MasterVRF')
                ->andWhere('ParentID = 0')
                ->andWhere('AFI = 4')
                ->setParameter('MasterVRF', $data['VRFID'])
                ->execute()
                ->fetch();
            $IPv6Root = $queryBuilder
                ->select('PrefixID')
                ->from('prefixes')
                ->where('MasterVRF = :MasterVRF')
                ->andWhere('ParentID = 0')
                ->andWhere('AFI = 6')
                ->setParameter('MasterVRF', $data['VRFID'])
                ->execute()
                ->fetch();
            $vrfs[] = array(
                "VRFName"   =>  $data['VRFName'],
                "VRFID" =>  $data['VRFID'],
                "VRFDescription"    =>  $data['VRFDescription'],
                "IPv4Root"  =>  $IPv4Root['PrefixID'],
                "IPv6Root"  =>  $IPv6Root['PrefixID'],
            );
        }

        return $vrfs;
    }

    /**
     * @param int $ID
     * @return array
     */
    public static function getAllExcept(int $ID): array
    {
        global $EntityManager;



        $queryBuilder = $EntityManager->createQueryBuilder();

        $select = $queryBuilder
            ->select('v.vrfid', 'v.vrfname', 'v.vrfdescription', 'v.vrfrd', 'v.vrfrt')
            ->from('Entity\Vrfs', 'v')
            ->where('v.vrfid != :VRFID')
            ->setParameter('VRFID', $ID)
            ->getQuery()
            ->getArrayResult();


        return $select;
    }

    public function delete(): bool
    {
        try {
            $this->em->getConnection()->beginTransaction();
            $query = $this->em->createQuery('Delete FROM \Entity\Prefixes p WHERE p.mastervrf = :MasterVRF');
            $query->setParameter('MasterVRF', $this->entity->getVrfid());
            $query->execute();
            $this->em->remove($this->entity);
            $this->em->flush();
            $this->em->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            throw new RuntimeException($e);

            $this->em->getConnection()->rollBack();

            return false;
        }
    }


    /**
     * @return \Entity\Vrfs|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
