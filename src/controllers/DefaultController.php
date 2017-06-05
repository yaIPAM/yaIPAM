<?php

namespace Controller;

class DefaultController extends BaseController
{
    public function IndexAction()
    {
        global $auditManager;

        $auditReader = $auditManager->createAuditReader($this->getEM());


        $this->view();
    }
}
