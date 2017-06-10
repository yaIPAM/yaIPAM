<?php
namespace Controller;
use \Framework\BaseController;
use \Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * User: ktammling
 * Date: 23.05.17
 * Time: 19:40
 */
class LoginController extends BaseController
{
    public function IndexAction()
    {
        $User = new \Service\User($this->em);

        if ($this->req->request->getBoolean('submit') && \Service\User::checkCSFR($this->req->request->get('csfr'))) {
            if ($User->Authenticate($this->req->request->get('Username'), $this->req->request->get('Password'))) {
                $response = new RedirectResponse(SITE_BASE);
                $response->send();
            } else {
                \MessageHandler::Error(_('Login failure'), _('The username and password combination you have entered is incorrect.'));
            }
        }

        $this->set('D_CSFR', $_SESSION['csfr']);

        $this->view();
    }

    public function LogoutAction()
    {
        $User = new \Service\User($this->em);

        if ($User->Logout()) {
            $response = new RedirectResponse(SITE_BASE);
            $response->send();
        } else {
            \MessageHandler::Error(_('General error'), _('Error destroying user session.'));
        }
    }
}
