<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OtvDomains
 *
 * @ORM\Table(name="otv_domains")
 * @ORM\Entity
 */
class OtvDomains
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
     * @ORM\Column(name="OTVID", type="integer", nullable=false)
     */
    private $otvid;

    /**
     * @var integer
     *
     * @ORM\Column(name="DomainID", type="integer", nullable=false)
     */
    private $domainid;


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
     * Set otvid
     *
     * @param integer $otvid
     *
     * @return OtvDomains
     */
    public function setOtvid($otvid)
    {
        $this->otvid = $otvid;

        return $this;
    }

    /**
     * Get otvid
     *
     * @return integer
     */
    public function getOtvid()
    {
        return $this->otvid;
    }

    /**
     * Set domainid
     *
     * @param integer $domainid
     *
     * @return OtvDomains
     */
    public function setDomainid($domainid)
    {
        $this->domainid = $domainid;

        return $this;
    }

    /**
     * Get domainid
     *
     * @return integer
     */
    public function getDomainid()
    {
        return $this->domainid;
    }
}
