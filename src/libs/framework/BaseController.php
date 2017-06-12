<?php
namespace Framework;

/**
 * Basecontroller.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 24.05.17
 * Time: 14:24
 */
class BaseController
{
    protected $_controller = "";
    protected $_action = "";
    protected $_template;
    protected $_tplfile = "";
    protected $req;
    protected $em;
    protected $auditManager;

    /**
     * @param string $controller
     * @param string $action
     */
    public function __construct($controller, $action)
    {
        global $tpl, $request, $EntityManager, $auditManager;

        $this->_controller = $controller;
        $this->_action = $action;
        $this->_template = $tpl;
        $this->_tplfile = strtolower(str_replace('Controller', '', $controller)).'/'.strtolower(str_replace('Action', '', $this->_action)).'.html';
        $this->req = $request;
        $this->em = $EntityManager;
        $this->auditManager = $auditManager;
    }

    /**
     * @return \SimpleThings\EntityAudit\AuditManager
     */
    protected function getAuditManager()
    {
        return $this->auditManager;
    }

    protected function getEM()
    {
        return $this->em;
    }

    /**
     * @param $name
     * @param null $value
     */
    protected function set($name, $value = null)
    {
        if ($value == null) {
            $this->_template->assign($name);
        } else {
            $this->_template->assign($name, $value);
        }
    }

    protected function CheckRequired(array $required)
    {
        foreach ($required as $req) {
            $req = trim($req);
            if (empty($req)) {
                return false;
            }
        }

        return true;
    }

    protected function CheckAccess($Access)
    {
        if (\Service\User::showGroup() >= $Access) {
            return true;
        } else {
            header("Location: ".SITE_BASE."/error/denied");
            return false;
        }
    }

    protected function view()
    {
        global $whoops;
        try {
            $this->set('S_ACTIVE_MENU', $this->_controller);

            if ($this->_template->templateExists($this->_tplfile)
                && !(Core::unitTestAlive())) {
                $this->_template->display($this->_tplfile);
            }

            return true;
        } catch (\Exception $e) {
            $whoops->handleException($e);

            return false;
        }
    }
}
