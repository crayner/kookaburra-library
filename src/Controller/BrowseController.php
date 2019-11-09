<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 3/11/2019
 * Time: 08:35
 */

namespace Kookaburra\Library\Controller;

use App\Form\Entity\SearchAny;
use App\Form\SearchAnyType;
use App\Provider\ProviderFactory;
use App\Twig\ModuleMenu;
use App\Twig\SidebarContent;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Manager\BrowsePagination;
use Kookaburra\Library\Manager\LibraryHelper;
use Kookaburra\Library\Manager\Traits\LibraryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BrowseController
 * @package Controller
 * @Route("/library", name="library__")
 */
class BrowseController extends AbstractController
{
    use LibraryControllerTrait;

    /**
     * browse
     * @param Request $request
     * @param BrowsePagination $pagination
     * @param LibraryHelper $helper
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/browse/", name="browse")
     * @IsGranted("ROLE_ROUTE")
     */
    public function browse(Request $request, BrowsePagination $pagination, SidebarContent $sidebar, LibraryHelper $helper)
    {
        // Hide the menu.
        $sidebar->setNoSidebar(true);
dd($sidebar);
        if ($request->getMethod() !== 'POST' && $request->getSession()->has('libraryBrowseSearch'))
            $search = $request->getSession()->get('libraryBrowseSearch');
        $search = isset($search) ? $search : new SearchAny();

        $form = $this->createForm(SearchAnyType::class, $search);

        $form->handleRequest($request);

        if ($form->get('clear')->isClicked()) {
            $search = new SearchAny();
            $form = $this->createForm(SearchAnyType::class, $search);
        }

        $provider = ProviderFactory::create(LibraryItem::class);
        $content = $provider->getBrowserList($search);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        $request->getSession()->set('libraryBrowseSearch', $search);

        return $this->render('@KookaburraLibrary/browse.html.twig',
            [
                'form' => $form->createView(),
                'top5' => ProviderFactory::getRepository(LibraryItemEvent::class)->findMonthlyTop5Loan(),
                'newTitles' => ProviderFactory::getRepository(LibraryItem::class)->findLatestCreated(),
                'library' => $helper::getCurrentLibrary(),
            ]
        );
    }

    /**
     * itemInformation
     * @param LibraryItem $item
     * @Route("/item/{item}/information", name="item_information")
     * @Security("is_granted('ROLE_ROUTE', ['library__browse'])")
     */
    public function itemInformation(LibraryItem $item)
    {
        return new JsonResponse([
            'content' => $this->renderView('@KookaburraLibrary/item_information.html.twig',
                [
                    'item' => $item,
                ]
            ),
            'header' => TranslationsHelper::translate('Library Item Details', ['{name}' => $item->getName()], 'Library'),
        ], 200);
    }
}