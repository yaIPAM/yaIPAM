<?php
/**
 * Changelog.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 04.06.17
 * Time: 14:08
 */

namespace Controller;

use MessageHandler;
use Service\Prefixes;
use Service\User;

class ChangelogController extends BaseController
{
    /**
     * @param int $subnetID
     * @return bool
     */
    public function PrefixAction($subnetID = 0)
    {
        $this->CheckAccess(User::GROUP_USER);

        $auditReader = $this->getAuditManager()->createAuditReader($this->getEM());

        $Subnet = new Prefixes($this->em);
        $Subnet->getByID($subnetID);

        if ($Subnet->getEntity() == null) {
            MessageHandler::Error(_('Not found'), _('Changelog for the requested prefix could not be found.'));
            return $this->view();
        }

        $this->set(array(
            "D_Prefix"  =>  $Subnet->getEntity()->getPrefix()."/".$Subnet->getEntity()->getPrefixlength(),
            "D_PrefixID"    =>  $Subnet->getEntity()->getPrefixid(),
        ));

        $revisions = $auditReader->findRevisions('Entity\Prefixes', $Subnet->getEntity()->getPrefixid());
        $auditRevisions = array();

        foreach ($revisions as $revision) {
            $entity = $auditReader->find('Entity\Prefixes', $Subnet->getEntity()->getPrefixid(), $revision->getRev());
            $auditRevisions[] = array(
                "rev"   =>  $revision->getRev(),
                "username"  =>  $revision->getUsername(),
                "timestamp" =>  $revision->getTimestamp()->format('d/m/Y H:i'),
                "prefix"    =>  $entity->getPrefix().'/'.$entity->getPrefixlength(),
                "prefixdescription" =>  $entity->getPrefixdescription(),
            );
        }

        $this->set(array(
            "D_AuditRevisions_Prefixes"  =>  $auditRevisions,
        ));

        return $this->view();
    }
}
