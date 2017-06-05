<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrefixesVrfs
 *
 * @ORM\Table(name="prefixes_vrfs")
 * @ORM\Entity
 */
class PrefixesVrfs
{
    /**
     * @var integer
     *
     * @ORM\Column(name="PrefixID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $prefixid;

    /**
     * @var integer
     *
     * @ORM\Column(name="VRFID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vrfid;

    /**
     * @var integer
     *
     * @ORM\Column(name="ParentID", type="integer", nullable=false)
     */
    private $parentid;


    /**
     * Set prefixid
     *
     * @param integer $prefixid
     *
     * @return PrefixesVrfs
     */
    public function setPrefixid($prefixid)
    {
        $this->prefixid = $prefixid;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

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
     * Set vrfid
     *
     * @param integer $vrfid
     *
     * @return PrefixesVrfs
     */
    public function setVrfid($vrfid)
    {
        $this->vrfid = $vrfid;

        return $this;
    }

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
     * Set parentid
     *
     * @param integer $parentid
     *
     * @return PrefixesVrfs
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
}
