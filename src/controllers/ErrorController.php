<?php
/**
 * ErrorController.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 24.05.17
 * Time: 15:13
 */

namespace Controller;
use \Framework\BaseController;

class ErrorController extends BaseController
{
    public function NotfoundAction()
    {
        return $this->view();
    }

    public function DeniedAction()
    {
        return $this->view();
    }
}
