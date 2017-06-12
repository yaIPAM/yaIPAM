<?php
namespace Service;

/**
 * Model_VLAN_Domainphp
 * Project: yaipam
 * User: ktammling
 * Date: 12.04.17
 * Time: 16:02
 */
class VlanDomains
{
    private $em;
    private $entity;

    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\VlanDomains();
    }

    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public static function listDomains(): array
    {
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
    public function selectFirst(): array
    {
        global $EntityManager, $whoops;
        $queryBuilder = $EntityManager->createQueryBuilder();



        try {
            $domains = $queryBuilder
                ->select('d.domainName', 'd.domainId', 'd.domainDescription')
                ->from('Entity\VlanDomains', 'd')
                ->setMaxResults(1)
                ->orderBy('d.domainName')
                ->getQuery()
                ->getSingleResult();

            return $domains;

        } catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    /**
     * @param int $ID
     * @return array
     */
    public function selectByID(int $ID)
    {
        $domain = $this->em->find("Entity\VlanDomains", $ID);

        $this->entity = $domain;

        return $domain;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        global $whoops;

        try {
            $this->em->persist($this->entity);
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
    public function delete(): bool
    {
        global $whoops;

        try {
            $this->em->beginTransaction();
            \Service\Vlans::DeleteAllByDomain($this->entity->getDomainId());
            $this->em->remove($this->entity);
            $this->em->flush();
            $this->em->commit();

            return true;
        } catch (\Exception $e) {
            $this->em->rollBack();
            $whoops->handleException($e);
            return false;
        }
    }
}
