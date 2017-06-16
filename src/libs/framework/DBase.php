<?php
/**
 * Created by PhpStorm.
 * User: ktammling
 * Date: 16.06.17
 * Time: 14:56
 */

namespace Framework;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL;
use SimpleThings\EntityAudit\AuditConfiguration;
use SimpleThings\EntityAudit\AuditManager;
use Doctrine\Common\EventManager;

class DBase {

    protected $dbal;
    protected $entityManager;

    public function __construct($Config)
    {

        $dbal_config = new DBAL\Configuration();
        $this->dbal = DriverManager::getConnection($Config['dbase'], $dbal_config);
        $orm_config = Setup::createAnnotationMetadataConfiguration(array(SCRIPT_BASE.'/src/entities'), $Config['general']['devMode'], null, null, false);
        if (extension_loaded('apcu')) {
            $orm_config->setQueryCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
            $orm_config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
            $orm_config->setResultCacheImpl(new \Doctrine\Common\Cache\ApcuCache());
        }
        $auditconfig = new AuditConfiguration();
        $auditconfig->setAuditedEntityClasses(array(
            'Entity\Addresses',
            'Entity\Prefixes',
            'Entity\User',
            'Entity\Vrfs'
        ));
        $auditconfig->setGlobalIgnoreColumns(array(
            'created_at',
            'updated_at'
        ));
        $auditconfig->setUsernameCallable(function() {
            return $_SESSION['Username'];
        });
        $evm = new EventManager();
        $auditManager = new AuditManager($auditconfig);
        $auditManager->registerEvents($evm);
        $EntityManager = EntityManager::create($Config['dbase'], $orm_config, $evm);
        $EntityManager->getConfiguration()->addCustomStringFunction('inet_aton', 'Application\DQL\InetAtonFunction');
        $EntityManager->getConfiguration()->addCustomStringFunction('inet6_aton', 'Application\DQL\Inet6AtonFunction');
        $EntityManager->getConfiguration()->addCustomStringFunction('MATCH_AGAINST', 'Application\DQL\MatchAgainstFunction');
        $this->entityManager = $EntityManager;

        return true;

    }

    /**
     * @return DBAL\Connection
     */
    public function getDbal()
    {
        return $this->dbal;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }



}