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
    public function save(): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        if ($this->getAddressID() == 0) {

            $queryBuilder
                ->insert('addresses')
                ->setValue('Address', ':Address')
                ->setValue('AddressAFI', ':AddressAFI')
                ->setValue('AddressState', ':AddressState')
                ->setValue('AddressName', ':AddressName')
                ->setValue('AddressFQDN', ':AddressFQDN')
                ->setValue('AddressMAC', ':AddressMAC')
                ->setValue('AddressTT', ':AddressTT')
                ->setValue('AddressDescription', ':AddressDescription')
                ->setValue('AddressPrefix', ':AddressPrefix');


        }
        else {
            $queryBuilder
                ->update('addresses')
                ->set('Address', ':Address')
                ->set('AddressAFI', ':AddressAFI')
                ->set('AddressState', ':AddressState')
                ->set('AddressName', ':AddressName')
                ->set('AddressFQDN', ':AddressFQDN')
                ->set('AddressMAC', ':AddressMAC')
                ->set('AddressTT', ':AddressTT')
                ->set('AddressDescription', ':AddressDescription')
                ->set('AddressPrefix', ':AddressPrefix')
                ->where('AddressID = :AddressID')
                ->setParameter('AddressID', $this->getAddressID());
        }

        $queryBuilder
            ->setParameter('Address', $this->getAddress(true))
            ->setParameter('AddressAFI', $this->getAddressAFI())
            ->setParameter('AddressState', $this->getAddressState())
            ->setParameter('AddressName', $this->getAddressName())
            ->setParameter('AddressFQDN', $this->getAddressFQDN())
            ->setParameter('AddressMAC', $this->getAddressMAC())
            ->setParameter('AddressTT', $this->getAddressTT())
            ->setParameter('AddressDescription', $this->getAddressDescription())
            ->setParameter('AddressPrefix', $this->getAddressPrefix());

        if ($queryBuilder->execute() === false) {
            return false;
        }

        return true;


    }

    public function getAddressByID(int $AddressID): array {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('*')
            ->from('addresses')
            ->where('AddressID = :AddressID')
            ->setParameter('AddressID', $AddressID)
            ->execute()
            ->fetch();


        $this->setAddressID($select['AddressID']);
        $this->setAddressAFI($select['AddressAFI']);
        $this->setAddress($select['Address'], true);
        $this->setAddressState($select['AddressState']);
        $this->setAddressName($select['AddressName']);
        $this->setAddressFQDN($select['AddressFQDN']);
        $this->setAddressMAC($select['AddressMAC']);
        $this->setAddressTT($select['AddressTT']);
        $this->setAddressDescription($select['AddressDescription']);
        $this->setAddressPrefix($select['AddressPrefix']);


        return (array)$select;
    }

    public function delete(): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $queryBuilder
            ->delete('addresses')
            ->where('AddressID = :AddressID')
            ->setParameter('AddressID', $this->getAddressID());

        if ($queryBuilder->execute() === false) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function deleteByPrefix(int $PrefixID): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $queryBuilder
            ->delete('addresses')
            ->where('AddressPrefix = :PrefixID')
            ->setParameter('PrefixID', $PrefixID);

        if ($queryBuilder->execute() === false) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function calcNewPrefix(int $OldPrefixID, int $VRF, int $AFI) {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('AddressID', 'Address', 'AddressAFI')
            ->from('addresses')
            ->where('AddressPrefix = :AddressPrefix')
            ->setParameter('AddressPrefix', $OldPrefixID)
            ->execute()
            ->fetchAll();

        foreach ($select as $data) {

            $queryBuilder = $dbal->createQueryBuilder();
            $Address = ($data['AddressAFI'] == 4) ? long2ip($data['Address']) : long2ip6($data['Address']);
            $NewPrefixID = self::getParentID($Address, $VRF, $AFI);

            $queryBuilder
                ->update('addresses')
                ->set('AddressPrefix', $NewPrefixID)
                ->where('AddressID = :AddressID')
                ->setParameter('AddressID', $data['AddressID']);

            if ($queryBuilder->execute() === false) {
                return false;
            }
        }

        return true;

    }

    public static function AddressExists(string $Address, int $VRF): bool {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $AFI = IPLib\Factory::addressFromString($Address);

        $select = $queryBuilder
            ->select('COUNT(*) AS total')
            ->from('addresses', 'a')
            ->innerjoin('a', 'prefixes', 'p', 'a.AddressPrefix = p.PrefixID')
            ->where('AddressAFI = :AddressAFI')
            ->andWhere('p.MasterVRF = :MasterVRF')
            ->andWhere('Address = :Address')
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AddressAFI', $AFI->getAddressType())
            ->setParameter('Address', ($AFI->getAddressType()==4) ? ip2long($Address) : ip2long6($Address))
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
    public static function listAddresses(int $PrefixID): array {
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
                }
                else if ($freedata->numeric() == $lastIP) {
                    $type = "broadcast";
                }
                else {
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
                }
                else {
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
    public static function getParentID(string $Address, int $VRF, int $AFI): int {
        global $dbal;

        $queryBuilder = $dbal->createQueryBuilder();

        $select = $queryBuilder
            ->select('PrefixID')
            ->from('prefixes')
            ->where(':address between RangeFrom and RangeTo')
            ->andWhere('MasterVRF = :MasterVRF')
            ->andWhere('AFI = :AFI')
            ->orderBy('PrefixLength', 'DESC')
            ->setParameter('address', IPLib\Factory::addressFromString($Address)->getComparableString())
            ->setParameter('MasterVRF', $VRF)
            ->setParameter('AFI', $AFI)
            ->execute()
            ->fetch();

        return (int)$select['PrefixID'];
    }

    public function getEntity() {
        return $this->entity;
    }
}