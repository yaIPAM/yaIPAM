<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VlanDomains
 *
 * @ORM\Table(name="vlan_domains")
 * @ORM\Entity
 */
class VlanDomains
{
    /**
     * @var integer
     *
     * @ORM\Column(name="domain_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $domainId;

    /**
     * @var string
     *
     * @ORM\Column(name="domain_name", type="string", length=255, nullable=false)
     */
    private $domainName;

    /**
     * @var string
     *
     * @ORM\Column(name="domain_description", type="string", length=255, nullable=false)
     */
    private $domainDescription;


    /**
     * Get domainId
     *
     * @return integer
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * Set domainName
     *
     * @param string $domainName
     *
     * @return VlanDomains
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;

        return $this;
    }

    /**
     * Get domainName
     *
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Set domainDescription
     *
     * @param string $domainDescription
     *
     * @return VlanDomains
     */
    public function setDomainDescription($domainDescription)
    {
        $this->domainDescription = $domainDescription;

        return $this;
    }

    /**
     * Get domainDescription
     *
     * @return string
     */
    public function getDomainDescription()
    {
        return $this->domainDescription;
    }
}
