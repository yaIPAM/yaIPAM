<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Addresses
 *
 * @ORM\Table(name="addresses", indexes={@ORM\Index(columns={"addressname", "addressfqdn", "addressdescription"},flags={"fulltext"})})
 * @ORM\Entity
 *
 */
class Addresses
{
    /**
     * @var integer
     *
     * @ORM\Column(name="AddressID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $addressid;

    /**
     * @var binary
     *
     * @ORM\Column(name="Address", type="binary", nullable=false)
     */
    private $address;

    /**
     * @var integer
     *
     * @ORM\Column(name="AddressAFI", type="integer", nullable=false)
     */
    private $addressafi;

    /**
     * @var integer
     *
     * @ORM\Column(name="AddressState", type="integer", length=11, nullable=false)
     */
    private $addressstate;

    /**
     * @var string
     *
     * @ORM\Column(name="AddressName", type="string", length=255, nullable=false)
     */
    private $addressname;

    /**
     * @var string
     *
     * @ORM\Column(name="AddressFQDN", type="string", length=255, nullable=false)
     */
    private $addressfqdn;

    /**
     * @var string
     *
     * @ORM\Column(name="AddressMAC", type="string", length=8, nullable=false)
     */
    private $addressmac;

    /**
     * @var string
     *
     * @ORM\Column(name="AddressTT", type="string", length=255, nullable=false)
     */
    private $addresstt;

    /**
     * @var string
     *
     * @ORM\Column(name="AddressDescription", type="text", nullable=false)
     */
    private $addressdescription;

    /**
     * @var integer
     *
     * @ORM\Column(name="AddressPrefix", type="integer", nullable=false)
     */
    private $addressprefix;


    /**
     * Get addressid
     *
     * @return integer
     */
    public function getAddressid()
    {
        return $this->addressid;
    }

    /**
     * Set address
     *
     * @param binary $address
     *
     * @return Addresses
     */
    public function setAddress($address)
    {
        $IP = \IP::create($address);
        $this->address = $IP->numeric();
        $this->addressafi = $IP->getVersion();

        return $this;
    }

    /**
     * Get address
     *
     * @return binary
     */
    public function getAddress()
    {
        if (is_resource($this->address)) {
            $this->address = stream_get_contents($this->address);
        }

        if ($this->getAddressafi() == 4) {
            return long2ip($this->address);
        } elseif ($this->getAddressafi() == 6) {
            return long2ip6($this->address);
        }

        return $this->address;
    }

    /**
     * Set addressafi
     *
     * @param integer $addressafi
     *
     * @return Addresses
     */
    public function setAddressafi($addressafi)
    {
        $this->addressafi = $addressafi;

        return $this;
    }

    /**
     * Get addressafi
     *
     * @return integer
     */
    public function getAddressafi()
    {
        return $this->addressafi;
    }

    /**
     * Set addressstate
     *
     * @param boolean $addressstate
     *
     * @return Addresses
     */
    public function setAddressstate($addressstate)
    {
        $this->addressstate = $addressstate;

        return $this;
    }

    /**
     * Get addressstate
     *
     * @return boolean
     */
    public function getAddressstate()
    {
        return $this->addressstate;
    }

    /**
     * Set addressname
     *
     * @param string $addressname
     *
     * @return Addresses
     */
    public function setAddressname($addressname)
    {
        $this->addressname = $addressname;

        return $this;
    }

    /**
     * Get addressname
     *
     * @return string
     */
    public function getAddressname()
    {
        return $this->addressname;
    }

    /**
     * Set addressfqdn
     *
     * @param string $addressfqdn
     *
     * @return Addresses
     */
    public function setAddressfqdn($addressfqdn)
    {
        $this->addressfqdn = $addressfqdn;

        return $this;
    }

    /**
     * Get addressfqdn
     *
     * @return string
     */
    public function getAddressfqdn()
    {
        return $this->addressfqdn;
    }

    /**
     * Set addressmac
     *
     * @param string $addressmac
     *
     * @return Addresses
     */
    public function setAddressmac($addressmac)
    {
        $this->addressmac = $addressmac;

        return $this;
    }

    /**
     * Get addressmac
     *
     * @return string
     */
    public function getAddressmac()
    {
        return $this->addressmac;
    }

    /**
     * Set addresstt
     *
     * @param string $addresstt
     *
     * @return Addresses
     */
    public function setAddresstt($addresstt)
    {
        $this->addresstt = $addresstt;

        return $this;
    }

    /**
     * Get addresstt
     *
     * @return string
     */
    public function getAddresstt()
    {
        return $this->addresstt;
    }

    /**
     * Set addressdescription
     *
     * @param string $addressdescription
     *
     * @return Addresses
     */
    public function setAddressdescription($addressdescription)
    {
        $this->addressdescription = $addressdescription;

        return $this;
    }

    /**
     * Get addressdescription
     *
     * @return string
     */
    public function getAddressdescription()
    {
        return $this->addressdescription;
    }

    /**
     * Set addressprefix
     *
     * @param integer $addressprefix
     *
     * @return Addresses
     */
    public function setAddressprefix($addressprefix)
    {
        $this->addressprefix = $addressprefix;

        return $this;
    }

    /**
     * Get addressprefix
     *
     * @return integer
     */
    public function getAddressprefix()
    {
        return $this->addressprefix;
    }
}
