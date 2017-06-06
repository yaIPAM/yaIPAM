<?php
/**
 * SearchController.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 29.05.17
 * Time: 11:54
 */

namespace Controller;

use \Framework\BaseController;

class SearchController extends BaseController
{
    public function SearchAction()
    {
        $this->CheckAccess(\Service\User::GROUP_USER);

        $SearchString = $this->req->request->get("search");
        $OrignalSearchString = $SearchString;
        $FulltextSearch = trim($SearchString, '*')."*";

        if (empty($OrignalSearchString)) {
            \MessageHandler::Error(_('Searchstring too short'), _("The search string seems to be empty and won't work like this."));
            return $this->view();
        }

        try {
            $IPSearch = \IP::create($SearchString);
            $IPSearch = $this->em->createQueryBuilder('a')
                ->select('a.addressid', 'a.address', 'a.addressdescription', 'a.addressname', 'a.addressstate', 'a.addressfqdn', 'a.addressmac', 'a.addresstt', 'a.addressprefix')
                ->where("a.address LIKE :address")
                ->from('Entity\Addresses', 'a')
                ->setParameter('address', $IPSearch->numeric())
                ->getQuery()
                ->getArrayResult();
        } catch (\InvalidArgumentException $e) {
            $IPSearch = null;
        }

        try {
            $SearchStringArray = explode("/", $SearchString);
            $Prefix = $SearchStringArray[0];
            $PrefixLength = (isset($SearchStringArray[1])) ? $SearchStringArray[1] : null;

            $NetworkSearch = \IP::create($Prefix);
            $NetworkSearch = $this->em->createQueryBuilder('p')
                ->select('p.prefixid', 'p.prefix', 'p.prefixdescription', 'p.prefixlength')
                ->where("p.prefix LIKE :prefix")
                ->from('Entity\Prefixes', 'p')
                ->setParameter('prefix', $NetworkSearch->numeric());

            if ($PrefixLength != null) {
                $NetworkSearch->andWhere("p.prefixlength = :prefixlength")
                    ->setParameter("prefixlength", $PrefixLength);
            }

            $NetworkSearch = $NetworkSearch->getQuery()->getArrayResult();
        } catch (\InvalidArgumentException $e) {
            $NetworkSearch = null;
        }

        if (strlen($SearchString)-1 < 4) {
            \MessageHandler::Warning(_('Searchstring too short'), _('The search string must contain at least 4 characters for the search being most effective.'));
        }

        $result = $this->em->createQueryBuilder('a')
            ->select('a.addressid', 'a.address', 'a.addressdescription', 'a.addressname', 'a.addressstate', 'a.addressfqdn', 'a.addressmac', 'a.addresstt', 'a.addressprefix')
            ->where("MATCH_AGAINST (a.addressname, a.addressfqdn, a.addressdescription, :searchterm) > 0.0")
            ->from('Entity\Addresses', 'a')
            ->setParameter('searchterm', $FulltextSearch)
            ->getQuery()
            ->getResult();

        if ($IPSearch != null) {
            $result = array_merge($result, $IPSearch);
        }


        $this->set("D_Addresses", $result);

        $result = $this->em->createQueryBuilder('p')
            ->select('p.prefixid', 'p.prefix', 'p.prefixdescription', 'p.prefixlength')
            ->where("MATCH_AGAINST (p.prefixdescription, :searchterm) > 0.0")
            ->from('Entity\Prefixes', 'p')
            ->setParameter('searchterm', $FulltextSearch)
            ->getQuery()
            ->getResult();

        if ($NetworkSearch != null) {
            $result = array_merge($result, $NetworkSearch);
        }

        $this->set("D_Subnets", $result);

        $result = $this->em->createQueryBuilder('v')
            ->select('v.vlanid', 'v.vlanname')
            ->where("MATCH_AGAINST (v.vlanname, :searchterm) > 0.0")
            ->orWhere('v.vlanid = :searchtermid')
            ->from('Entity\Vlans', 'v')
            ->setParameter('searchterm', $FulltextSearch)
            ->setParameter('searchtermid', $SearchString)
            ->getQuery()
            ->getArrayResult();

        $this->set("D_Vlans", $result);

        $this->set("D_SearchString", $OrignalSearchString);


        return $this->view();
    }
}
