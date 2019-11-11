<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 11/11/2019
 * Time: 10:41
 */

namespace Kookaburra\Library\Controller;

use App\Manager\ExcelManager;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\CatalogueSearchType;
use Kookaburra\Library\Manager\LibraryHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 * @package Kookaburra\Library\Controller
 * @Route("/library", name="library__")
 */
class ReportController extends AbstractController
{
    /**
     * catalogueSummary
     * @Security("is_granted('ROLE_ROUTE', ['library__manage_catalogue'])")
     */
    public function catalogueSummary(Request $request, ExcelManager $excelManager)
    {
        $search = isset($search) ? $search : new CatalogueSearch();

        $form = $this->createForm(CatalogueSearchType::class, $search);

        $form->handleRequest($request);

        $search->setLibrary($search->getLibrary() ?: LibraryHelper::getCurrentLibrary());

        $list = ProviderFactory::create(LibraryItem::class)->getCatalogueList($search, true);

        $excelManager->exportWithArray($list, sprintf('Catalogue_Summary_%s_%s.xlsx', $search->getLibrary()->getAbbr(), date('Ymd')), true);
    }
}