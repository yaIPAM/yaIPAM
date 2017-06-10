<?php

namespace Controller;
use \Framework\BaseController;

class DefaultController extends BaseController
{
    public function IndexAction()
    {
        return $this->view();
    }
}
