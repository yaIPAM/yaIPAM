<?php
/**
 * User: ktammling
 * Date: 23.05.17
 * Time: 18:38
 */

namespace Service;

use Symfony\Component\Yaml\Exception\RuntimeException;

class User
{
    const GROUP_USER = 1;
    const GROUP_ADMINISTRATOR = 2;
    const GROUP_SYSTEMADMIN = 3;

    protected $em;
    protected $entity;

    /**
     * User constructor.
     * @param $EntityManager
     */
    public function __construct($EntityManager)
    {
        $this->em = $EntityManager;
        $this->entity = new \Entity\User();
    }


    /**
     * @param $UserID
     * @return bool
     */
    public function findbyID(int $UserID)
    {
        $this->entity = $this->em->find('\Entity\User', $UserID);

        if ($this->entity == null) {
            return false;
        } else {
            return $this->entity;
        }
    }

    /**
     * @param $Username
     * @return bool
     */
    public function UserExists($Username): bool
    {
        if ($this->em->getRepository('\Entity\User')->findOneByUsername($Username) == null) {
            return false;
        } else {
            return false;
        }
    }

    /**
     * @param $Username
     * @param $Password
     * @return bool
     */
    public function Authenticate(string $Username, string $Password): bool
    {
        $this->entity = $this->em->getRepository('\Entity\User')->findOneByUsername($Username);

        if ($this->entity == null) {
            return false;
        }

        if (password_verify($Password, $this->entity->getPassword())) {
            $_SESSION['login'] = true;
            $_SESSION['Username'] = $Username;
            $_SESSION['Group'] = $this->entity->getUsergroup();

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $Hash
     * @return bool
     */
    public static function checkCSFR(string $Hash): bool
    {
        if ($_SESSION['csfr'] == $Hash) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function Logout(): bool
    {
        unset($_SESSION['Username']);
        unset($_SESSION['Group']);
        $_SESSION['login'] = false;

        return true;
    }

    /**
     * @param int $Group
     * @return bool
     */
    public static function checkGroup(int $Group): bool
    {
        if ($_SESSION['Group'] == $Group) {
            return true;
        }

        return false;
    }

    public static function showGroup()
    {
        return $_SESSION['Group'];
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
