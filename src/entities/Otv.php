<?php
namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Otv
 *
 * @ORM\Table(name="otv")
 * @ORM\Entity
 */
class Otv
{
    /**
     * @var integer
     *
     * @ORM\Column(name="OTVID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $otvid;

    /**
     * @var string
     *
     * @ORM\Column(name="OTVName", type="string", length=255, nullable=false)
     */
    private $otvname;

    /**
     * @var string
     *
     * @ORM\Column(name="OTVDescription", type="string", length=255, nullable=false)
     */
    private $otvdescription;


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
     * Set otvname
     *
     * @param string $otvname
     *
     * @return Otv
     */
    public function setOtvname($otvname)
    {
        $this->otvname = $otvname;

        return $this;
    }

    /**
     * Get otvname
     *
     * @return string
     */
    public function getOtvname()
    {
        return $this->otvname;
    }

    /**
     * Set otvdescription
     *
     * @param string $otvdescription
     *
     * @return Otv
     */
    public function setOtvdescription($otvdescription)
    {
        $this->otvdescription = $otvdescription;

        return $this;
    }

    /**
     * Get otvdescription
     *
     * @return string
     */
    public function getOtvdescription()
    {
        return $this->otvdescription;
    }
}
