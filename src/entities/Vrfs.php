<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vrfs
 *
 * @ORM\Table(name="vrfs")
 * @ORM\Entity
 */
class Vrfs
{

    /**
     * @var integer
     *
     * @ORM\Column(name="VRFID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $vrfid;

    /**
     * @var string
     *
     * @ORM\Column(name="VRFName", type="string", length=255, nullable=false)
     */
    private $vrfname;

    /**
     * @var string
     *
     * @ORM\Column(name="VRFDescription", type="string", length=255, nullable=false)
     */
    private $vrfdescription;

    /**
     * @var string
     *
     * @ORM\Column(name="VRFRD", type="string", length=255, nullable=false)
     */
    private $vrfrd;

    /**
     * @var string
     *
     * @ORM\Column(name="VRFRT", type="string", length=255, nullable=false)
     */
    private $vrfrt;

    /**
     * Get vrfid
     *
     * @return integer
     */
    public function getVrfid()
    {
        return $this->vrfid;
    }

    /**
     * Set vrfname
     *
     * @param string $vrfname
     *
     * @return Vrfs
     */
    public function setVrfname($vrfname)
    {
        $this->vrfname = $vrfname;

        return $this;
    }

    /**
     * Get vrfname
     *
     * @return string
     */
    public function getVrfname()
    {
        return $this->vrfname;
    }

    /**
     * Set vrfdescription
     *
     * @param string $vrfdescription
     *
     * @return Vrfs
     */
    public function setVrfdescription($vrfdescription)
    {
        $this->vrfdescription = $vrfdescription;

        return $this;
    }

    /**
     * Get vrfdescription
     *
     * @return string
     */
    public function getVrfdescription()
    {
        return $this->vrfdescription;
    }

    /**
     * Set vrfrd
     *
     * @param string $vrfrd
     *
     * @return Vrfs
     */
    public function setVrfrd($vrfrd)
    {
        $this->vrfrd = $vrfrd;

        return $this;
    }

    /**
     * Get vrfrd
     *
     * @return string
     */
    public function getVrfrd()
    {
        return $this->vrfrd;
    }

    /**
     * Set vrfrt
     *
     * @param string $vrfrt
     *
     * @return Vrfs
     */
    public function setVrfrt($vrfrt)
    {
        $this->vrfrt = $vrfrt;

        return $this;
    }

    /**
     * Get vrfrt
     *
     * @return string
     */
    public function getVrfrt()
    {
        return $this->vrfrt;
    }

    /**
     * @return mixed
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * @param mixed $prefixes
     */
    public function setPrefixes($prefixes)
    {
        $this->prefixes = $prefixes;
    }


}

