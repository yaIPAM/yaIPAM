<?php
/**
 * User: ktammling
 * Date: 23.05.17
 * Time: 08:17
 */

namespace Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */

class User {
    /**
     * @var integer
     *
     * @ORM\Column(name="UserID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $userid;

    /**
     * @var string
     *
     * @ORM\Column(name="Username", type="string", length=255, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="Useremail", type="string", length=255, nullable=false)
     */
    private $useremail;

    /**
     * @var string
     *
     * @ORM\Column(name="Password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var integer
     *
     * @ORM\Column(name="Method", type="integer", length=11, nullable=false)
     */
    private $method;

    /**
     * @var integer
     *
     * @ORM\Column(name="UserGroup", type="integer", length=11, nullable=false)
     */
    private $usergroup;

    /**
     * @return int
     */
    public function getUserid(): int
    {
        return $this->userid;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUseremail(): string
    {
        return $this->useremail;
    }

    /**
     * @param string $useremail
     */
    public function setUseremail(string $useremail)
    {
        $this->useremail = $useremail;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @return int
     */
    public function getMethod(): int
    {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod(int $method)
    {
        $this->method = $method;
    }

    /**
     * @return int
     */
    public function getUsergroup(): int
    {
        return $this->usergroup;
    }

    /**
     * @param int $group
     */
    public function setUsergroup(int $usergroup)
    {
        $this->usergroup = $usergroup;
    }
}