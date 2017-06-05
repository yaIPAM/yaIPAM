<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vlans
 *
 * @ORM\Table(name="vlans", indexes={@ORM\Index(columns={"vlanname"},flags={"fulltext"})})
 * @ORM\Entity
 */
class Vlans
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="VlanID", type="integer", nullable=false)
     */
    private $vlanid;

    /**
     * @var string
     *
     * @ORM\Column(name="VlanName", type="string", length=32, nullable=false)
     */
    private $vlanname;

    /**
     * @var integer
     *
     * @ORM\Column(name="VlanDomain", type="integer", nullable=false)
     */
    private $vlandomain;

    /**
     * @var integer
     *
     * @ORM\Column(name="OTVDomain", type="integer", nullable=false)
     */
    private $otvdomain;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vlanid
     *
     * @param integer $vlanid
     *
     * @return Vlans
     */
    public function setVlanid($vlanid)
    {
        $this->vlanid = $vlanid;

        return $this;
    }

    /**
     * Get vlanid
     *
     * @return integer
     */
    public function getVlanid()
    {
        return $this->vlanid;
    }

    /**
     * Set vlanname
     *
     * @param string $vlanname
     *
     * @return Vlans
     */
    public function setVlanname($vlanname)
    {
        $this->vlanname = $vlanname;

        return $this;
    }

    /**
     * Get vlanname
     *
     * @return string
     */
    public function getVlanname()
    {
        return $this->vlanname;
    }

    /**
     * Set vlandomain
     *
     * @param integer $vlandomain
     *
     * @return Vlans
     */
    public function setVlandomain($vlandomain)
    {
        $this->vlandomain = $vlandomain;

        return $this;
    }

    /**
     * Get vlandomain
     *
     * @return integer
     */
    public function getVlandomain()
    {
        return $this->vlandomain;
    }

    /**
     * Set otvdomain
     *
     * @param integer $otvdomain
     *
     * @return Vlans
     */
    public function setOtvdomain($otvdomain)
    {
        $this->otvdomain = $otvdomain;

        return $this;
    }

    /**
     * Get otvdomain
     *
     * @return integer
     */
    public function getOtvdomain()
    {
        return $this->otvdomain;
    }
}
