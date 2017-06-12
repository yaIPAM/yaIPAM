<?php
namespace Service;

// @TODO Move this to an entity

/**
 * Created by PhpStorm.
 * User: ktammling
 * Date: 09.05.17
 * Time: 14:41
 */
class Addresses
{
    const STATE_FREE = 0;
    const STATE_ALLOCATED = 1;
    const STATE_RESERVED = 2;
    const STATE_EXPIRED = 3;

    protected $em;
    protected $entity;

    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\Addresses();
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

    public function getAddressByID(int $AddressID): bool
    {
        $this->entity = $this->em->find("Entity\Addresses", $AddressID);

        if ($this->entity != null) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        global $whoops;

        try {
            $this->em->remove($this->entity);
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            $whoops->handleException($e);
            return false;
        }
    }

    public static function deleteByPrefix(int $PrefixID): bool
    {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $queryBuilder
            ->delete('addresses')
            ->where('AddressPrefix = :PrefixID')
            ->setParameter('PrefixID', $PrefixID);

        if ($queryBuilder->execute() === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function calcNewPrefix(int $OldPrefixID, int $VRF, int $AFI)
    {
        global $EntityManager;

        $queryBuilder = $EntityManager->createQueryBuilder();

        $select = $queryBuilder
            ->select('a.addressid', 'a.address', 'a.addressafi')
            ->from('Entity\Addresses', 'a')
            ->where('a.addressprefix = :AddressPrefix')
            ->setParameter('AddressPrefix', $OldPrefixID)
            ->getQuery()
            ->getArrayResult();

        foreach ($select as $data) {
            $address = stream_get_contents($data['address']);
            $Address = ($data['addressafi'] == 4) ? long2ip($address) : long2ip6($address);
            $NewPrefixID = self::getParentID($Address, $VRF, $AFI);

            $AddressEntity = $EntityManager->find('Entity\Addresses', $data['addressid']);

            $AddressEntity->setAddressprefix($NewPrefixID);
            try {
                $EntityManager->persist($AddressEntity);
                $EntityManager->flush();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    public static function AddressExists(string $Address, int $VRF): bool
    {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $AFI = \IP::create($Address);

        $select = $queryBuilder
            ->select('COUNT(*) AS total')
            ->from('addresses', 'a')
            ->innerjoin('a', 'prefixes', 'p', 'a.AddressPrefix = p.PrefixID')
            ->where('AddressAFI = :AddressAFI')
            ->andWhere('p.MasterVRF = :MasterVRF')
            ->andWhere('Address = :Address')
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AddressAFI', $AFI->getVersion())
            ->setParameter('Address', $AFI->numeric())
            ->execute()
            ->fetch();

        if ($select['total'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function listAddresses(int $PrefixID): array
    {
        global $EntityManager;

        $queryBuilder = $EntityManager->createQueryBuilder();

        $select = $queryBuilder
            ->select('a.addressid, a.addressafi, a.address, a.addressstate, a.addressname, a.addressfqdn, a.addressmac, a.addresstt')
            ->from('Entity\Addresses', 'a')
            ->where('a.addressprefix = :PrefixID')
            ->setParameter('PrefixID', $PrefixID)
            ->orderBy('a.address', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $queryBuilder = $EntityManager->createQueryBuilder();
        $selectPrefix = $queryBuilder
            ->select('p.prefix', 'p.prefixlength')
            ->from('Entity\Prefixes', 'p')
            ->where('p.prefixid = :PrefixID')
            ->setParameter('PrefixID', $PrefixID)
            ->getQuery()
            ->getSingleResult();

        $free = \IPBlock::create(stream_get_contents($selectPrefix['prefix']).'/'.$selectPrefix['prefixlength']);
        $firstIP = $free->getFirstIp()->numeric();
        $lastIP = $free->getLastIp()->numeric();

        $addresses = array();
        $usedaddresses = array();

        if (!empty($select)) {
            foreach ($select as $data) {
                $address = stream_get_contents($data['address']);
                $address = \IP::create($address);

                $usedaddresses[$address->numeric()] = array(
                    "addressid" => $data['addressid'],
                    "addressafi" => $address->getVersion(),
                    "address" => $address->numeric(),
                    "addressstate" => $data['addressstate'],
                    "addressname" => $data['addressname'],
                    "addressfqdn" => $data['addressfqdn'],
                    "addressmac" => $data['addressmac'],
                    "addresstt" => $data['addresstt'],
                    "free" => false,
                );
            }

            foreach ($free as $freedata) {
                if ($freedata->numeric() == $firstIP) {
                    $type = "network";
                } elseif ($freedata->numeric() == $lastIP) {
                    $type = "broadcast";
                } else {
                    $type = "normal";
                }
                if (isset($usedaddresses[$freedata->numeric()])) {
                    $addresses[] = array(
                        "addressid" => $usedaddresses[$freedata->numeric()]['addressid'],
                        "addressafi" => $usedaddresses[$freedata->numeric()]['addressafi'],
                        "address" => $usedaddresses[$freedata->numeric()]['address'],
                        "addressstate" => $usedaddresses[$freedata->numeric()]['addressstate'],
                        "addressname" => $usedaddresses[$freedata->numeric()]['addressname'],
                        "addressfqdn" => $usedaddresses[$freedata->numeric()]['addressfqdn'],
                        "addressmac" => $usedaddresses[$freedata->numeric()]['addressmac'],
                        "addresstt" => $usedaddresses[$freedata->numeric()]['addresstt'],
                        "free" => false,
                        "type"  =>  $type,
                    );
                } else {
                    $addresses[] = array(
                        "address" => $freedata->numeric(),
                        "addressstate" => 0,
                        "addressname" => _('Free'),
                        "free" => true,
                        "addressafi" => $freedata->getVersion(),
                        "type"  =>  $type,
                    );
                }
            }
        }



        return (array)$addresses;
    }

    /**
     * @param string $Address
     * @param int $VRF
     * @return int
     */
    public static function getParentID(string $Address, int $VRF, int $AFI): int
    {
        global $EntityManager;

        $queryBuilder = $EntityManager->createQueryBuilder();

        $select = $queryBuilder
            ->select('p.prefixid')
            ->from('Entity\Prefixes', 'p')
            ->where(':address between p.rangefrom and p.rangeto')
            ->andWhere('p.mastervrf = :MasterVRF')
            ->andWhere('p.afi = :AFI')
            ->orderBy('p.prefixlength', 'DESC')
            ->setParameter('address', \IPLib\Factory::addressFromString($Address)->getComparableString())
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AFI', $AFI)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return (int)$select['prefixid'];
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
