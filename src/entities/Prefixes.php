<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prefixes
 *
 * @ORM\Table(name="prefixes", indexes={@ORM\Index(columns={"prefixdescription"},flags={"fulltext"})})
 * @ORM\Entity
 */
class Prefixes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="PrefixID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $prefixid;

    /**
     * @var binary
     *
     * @ORM\Column(name="Prefix", type="binary", nullable=false)
     */
    private $prefix;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrefixLength", type="integer", nullable=false)
     */
    private $prefixlength;

    /**
     * @var integer
     *
     * @ORM\Column(name="AFI", type="integer", nullable=false)
     */
    private $afi;

    /**
     * @var binary
     *
     * @ORM\Column(name="RangeFrom", type="binary", length=255, nullable=false)
     */
    private $rangefrom;

    /**
     * @var binary
     *
     * @ORM\Column(name="RangeTo", type="binary", length=255, nullable=false)
     */
    private $rangeto;

    /**
     * @var string
     *
     * @ORM\Column(name="PrefixDescription", type="string", length=255, nullable=false)
     */
    private $prefixdescription;

    /**
     * @var integer
     *
     * @ORM\Column(name="ParentID", type="integer", nullable=false)
     */
    private $parentid;

    /**
     * @var integer
     *
     * @ORM\Column(name="MasterVRF", type="integer", nullable=false)
     */
    private $mastervrf;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrefixState", type="integer", nullable=false)
     */
    private $prefixstate;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrefixVLAN", type="integer", nullable=false)
     */
    private $prefixvlan;


    /**
     * Get prefixid
     *
     * @return integer
     */
    public function getPrefixid()
    {
        return $this->prefixid;
    }

    /**
     * Set prefix
     *
     * @param binary $prefix
     *
     * @return Prefixes
     */
    public function setPrefix($prefix)
    {
        $Prefix = \IPBlock::create($prefix);

        $this->setAfi($Prefix->getVersion());

        if ($Prefix->getVersion() == 4) {
            $Prefix = explode("/", $Prefix);
            $this->prefix = ip2long($Prefix[0]);
        } elseif ($Prefix->getVersion() == 6) {
            $Prefix = explode("/", $Prefix);
            $this->prefix = ip2long6($Prefix[0]);
        }

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        if (is_resource($this->prefix)) {
            $prefix = stream_get_contents($this->prefix);
            rewind($this->prefix);
        } else {
            $prefix = $this->prefix;
        }

        if ($this->getAfi() == 4) {
            return long2ip($prefix);
        } elseif ($this->getAfi() == 6) {
            return long2ip6($prefix);
        }
    }

    /**
     * Set prefixlength
     *
     * @param integer $prefixlength
     *
     * @return Prefixes
     */
    public function setPrefixlength($prefixlength)
    {
        $this->prefixlength = $prefixlength;

        return $this;
    }

    /**
     * Get prefixlength
     *
     * @return integer
     */
    public function getPrefixlength()
    {
        return $this->prefixlength;
    }

    /**
     * Set afi
     *
     * @param integer $afi
     *
     * @return Prefixes
     */
    public function setAfi($afi)
    {
        $this->afi = $afi;

        return $this;
    }

    /**
     * Get afi
     *
     * @return integer
     */
    public function getAfi()
    {
        return $this->afi;
    }

    /**
     * Set rangefrom
     *
     * @param string $rangefrom
     *
     * @return Prefixes
     */
    public function setRangefrom($rangefrom)
    {
        $this->rangefrom = $rangefrom;

        return $this;
    }

    /**
     * Get rangefrom
     *
     * @return string
     */
    public function getRangefrom()
    {
        return $this->rangefrom;
    }

    /**
     * Set rangeto
     *
     * @param string $rangeto
     *
     * @return Prefixes
     */
    public function setRangeto($rangeto)
    {
        $this->rangeto = $rangeto;

        return $this;
    }

    /**
     * Get rangeto
     *
     * @return string
     */
    public function getRangeto()
    {
        return $this->rangeto;
    }

    /**
     * Set prefixdescription
     *
     * @param string $prefixdescription
     *
     * @return Prefixes
     */
    public function setPrefixdescription($prefixdescription)
    {
        $this->prefixdescription = $prefixdescription;

        return $this;
    }

    /**
     * Get prefixdescription
     *
     * @return string
     */
    public function getPrefixdescription()
    {
        return $this->prefixdescription;
    }

    /**
     * Set parentid
     *
     * @param integer $parentid
     *
     * @return Prefixes
     */
    public function setParentid($parentid)
    {
        $this->parentid = $parentid;

        return $this;
    }

    /**
     * Get parentid
     *
     * @return integer
     */
    public function getParentid()
    {
        return $this->parentid;
    }

    /**
     * Set mastervrf
     *
     * @param integer $mastervrf
     *
     * @return Prefixes
     */
    public function setMastervrf($mastervrf)
    {
        $this->mastervrf = $mastervrf;

        return $this;
    }

    /**
     * Get mastervrf
     *
     * @return integer
     */
    public function getMastervrf()
    {
        return $this->mastervrf;
    }

    /**
     * Set prefixstate
     *
     * @param integer $prefixstate
     *
     * @return Prefixes
     */
    public function setPrefixstate($prefixstate)
    {
        $this->prefixstate = $prefixstate;

        return $this;
    }

    /**
     * Get prefixstate
     *
     * @return integer
     */
    public function getPrefixstate()
    {
        return $this->prefixstate;
    }

    /**
     * Set prefixvlan
     *
     * @param integer $prefixvlan
     *
     * @return Prefixes
     */
    public function setPrefixvlan($prefixvlan)
    {
        $this->prefixvlan = $prefixvlan;

        return $this;
    }

    /**
     * Get prefixvlan
     *
     * @return integer
     */
    public function getPrefixvlan()
    {
        return $this->prefixvlan;
    }
}
