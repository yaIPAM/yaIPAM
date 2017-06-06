<?php

namespace Controller;
use \Framework\BaseController;

class DefaultController extends BaseController
{
    public function IndexAction()
    {
        global $auditManager;

        $auditReader = $auditManager->createAuditReader($this->getEM());


        $this->view();
    }
}
