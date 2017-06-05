<?php
namespace Controller;

/**
  * User: ktammling
 * Date: 24.05.17
 * Time: 14:08
 */
class UsersettingsController extends BaseController
{
    private $error = false;

    public function IndexAction()
    {
        $this->CheckAccess(\Service\User::GROUP_SYSTEMADMIN);

        $this->set('D_USERS', $this->em->getRepository('Entity\User')->findAll());

        return $this->view();
    }

    public function DeleteAction($UserID = 0)
    {
        $this->CheckAccess(\Service\User::GROUP_SYSTEMADMIN);

        $User = $this->em->find('Entity\User', $UserID);

        if ($User == null) {
            $this->_tplfile = 'usersettings/index.html';
            \MessageHandler::Error(_('User not found'), _('The selected user does not exist.'));

            return $this->IndexAction();
        }

        if ($this->req->request->get('submit') != null) {
            try {
                $this->em->remove($User);
                $this->em->flush();
                \MessageHandler::Success(_('User deleted'), _('The user has been deleted successfully.'));
                $this->_tplfile = 'usersettings/index.html';

                return $this->IndexAction();
            } catch (Exception $e) {
                throw new \RuntimeException($e);
            }
        }

        $this->set("D_User", $User);

        $this->view();
    }

    public function EditAction($UserID = 0)
    {
        $this->CheckAccess(\Service\User::GROUP_SYSTEMADMIN);

        $User = $this->em->find('Entity\User', $UserID);

        if ($this->req->request->get('submit') != null) {
            if ($User == null) {
                $this->_tplfile = 'usersettings/index.html';
                \MessageHandler::Error(_('User not found'), _('The selected user does not exist.'));
                $this->IndexAction();
                return $this->view();
            }

            if (!$this->CheckRequired($this->req->request->get('required'))) {
                \MessageHandler::Error(_('Empty fields'), _('Please fill out all fields'));
                $this->error = true;
            }

            $array = $this->req->request->get('required');

            $User->setUsername($array['Username']);
            if (!empty($this->req->request->get('Password'))) {
                $User->setPassword($this->req->request->get('Password'));
            }
            $User->setUseremail($array['Email']);
            $User->setMethod($array['Method']);
            $User->setUsergroup($array['Usergroup']);

            if (!$this->error) {
                try {
                    $this->em->persist($User);
                    $this->em->flush();
                    \MessageHandler::Success(_('User saved'), _('The user has been saved successfully.'));
                    $this->_tplfile = 'usersettings/index.html';
                    $this->IndexAction();
                    return $this->view();
                } catch (Exception $e) {
                    throw new \RuntimeException($e);
                }
            }
        }

        $this->set("D_User", $User);

        $this->view();
    }

    public function AddAction()
    {
        $this->CheckAccess(\Service\User::GROUP_SYSTEMADMIN);

        if ($this->req->request->get('submit') != null) {
            $User = new \Service\User($this->em);

            if ($User->UserExists($this->req->request->get('Username'))) {
                \MessageHandler::Error(_('Username exists'), _('The username you have entered already exists.'));
                $this->error = true;
            }

            if (!$this->CheckRequired($this->req->request->get('required'))) {
                \MessageHandler::Error(_('Empty fields'), _('Please fill out all fields'));
                $this->error = true;
            }
            if ($this->error) {
                $array = $this->req->request->getIterator('required');
                foreach ($array['required'] as $key => $value) {
                    $key = str_replace("'", '', trim($key));
                    $this->set($key, $value);
                }
                return $this->view();
            }

            $array = $this->req->request->get('required');

            $User->getEntity()->setUsername($array['Username']);
            $User->getEntity()->setPassword($array['Password']);
            $User->getEntity()->setUseremail($array['Email']);
            $User->getEntity()->setMethod($array['Method']);
            $User->getEntity()->setUsergroup($array['Usergroup']);

            try {
                $this->em->persist($User->getEntity());
                $this->em->flush();
                \MessageHandler::Success(_('User added'), _('The user has been added successfully.'));
                return $this->view();
            } catch (Exception $e) {
                throw new \RuntimeException($e);
            }
        }

        return $this->view();
    }
}
