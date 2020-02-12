<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 11/11/2019
 * Time: 10:41
 */

namespace Kookaburra\Library\Controller;

use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\ExcelManager;
use App\Provider\ProviderFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
use Kookaburra\Library\Entity\BorrowerIdentifierList;
use Kookaburra\Library\Entity\BorrowerSearch;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\IgnoreStatus;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Form\BorrowerIdentifierListType;
use Kookaburra\Library\Form\BorrowerSearchType;
use Kookaburra\Library\Form\CatalogueSearchType;
use Kookaburra\Library\Form\UserStatusType;
use Kookaburra\Library\Manager\BorrowerIdentifierReport;
use Kookaburra\Library\Manager\BorrowerListPDF;
use Kookaburra\Library\Manager\BorrowerPagination;
use Kookaburra\Library\Manager\LibraryHelper;
use Kookaburra\Library\Manager\Traits\LibraryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    use LibraryControllerTrait;

    /**
     * catalogueSummary
     * @Route("/catalogue/export/", name="catalogue_export")
     * @Security("is_granted('ROLE_ROUTE', ['library__manage_catalogue'])")
     * @param Request $request
     * @param ExcelManager $excelManager
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function catalogueSummary(Request $request, ExcelManager $excelManager)
    {
        dd($request->getSession());
        $search = isset($search) ? $search : new CatalogueSearch();

        $form = $this->createForm(CatalogueSearchType::class, $search);

        $form->handleRequest($request);

        $search->setLibrary($search->getLibrary() ?: LibraryHelper::getCurrentLibrary());

        $list = ProviderFactory::create(LibraryItem::class)->getCatalogueList($search, true);

        $excelManager->exportWithArray($list, sprintf('Catalogue_Summary_%s_%s.xlsx', $search->getLibrary()->getAbbr(), date('Ymd')), true);
    }

    /**
     * borrowingRecord
     * @param Person $person
     * @Route("/report/borrowing/record/", name="report_borrow_record")
     * @IsGranted("ROLE_ROUTE")
     */
    public function borrowingRecord(Request $request, BorrowerPagination $pagination)
    {
        $search = isset($search) ? $search : new BorrowerSearch();
        $search->setLibrary($search->getLibrary() ?: LibraryHelper::getCurrentLibrary());

        $form = $this->createForm(BorrowerSearchType::class, $search);

        $form->handleRequest($request);

        if ($form->get('clear')->isClicked()) {
            $search = new BorrowerSearch();
            $form = $this->createForm(BorrowerSearchType::class, $search);
        }

        if ($search->getPerson() instanceof Person)
            $events = ProviderFactory::create(LibraryItemEvent::class)->findBorrowerRecords($search);
        else
            $events = [];

        $pagination->setContent($events)->setPageMax(25)
            ->setPaginationScript();

        return $this->render('@KookaburraLibrary/borrower_search.html.twig',
            [
                'form' => $form->createView(),
                'events' => $events,
            ]
        );
    }

    /**
     * overdueReport
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/overdue/report/", name="overdue_item_report")
     * @IsGranted("ROLE_ROUTE")
     */
    public function overdueReport(Request $request)
    {
        $status = new IgnoreStatus();

        $form = $this->createForm(UserStatusType::class, $status, ['action' => $this->generateUrl('library__overdue_item_report')]);

        $form->handleRequest($request);

        $overdue = ProviderFactory::getRepository(LibraryItem::class)->findOverdue($status);

        return $this->render('@KookaburraLibrary/overdue_report.html.twig',
            [
                'form' => $form->createView(),
                'overdue' => $overdue,
            ]
        );
    }

    /**
     * borrowerIdentifierReport
     * @param Request $request
     * @Route("/borrower/identifier/report/", name="borrower_identifier_report")
     * @IsGranted("ROLE_ROUTE")
     */
    public function borrowerIdentifierReport(Request $request, BorrowerListPDF $pdf)
    {
        $data = new BorrowerIdentifierList();
        $form = $this->createForm(BorrowerIdentifierListType::class, $data);
        $form->handleRequest($request);

        $people = ProviderFactory::create(Library::class)->findPeopleFormIdentifierReport($form);

        if ($form->get('print')->isClicked()) {
            $pdf->setForm($form)->setPeople($people)->generate();
        }

        return $this->render('@KookaburraLibrary/borrower_identifier_list.html.twig',
            [
                'form' => $form->createView(),
                'people' => $people,
            ]
        );
    }
}