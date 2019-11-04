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

use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\BrowseSearchType;
use Kookaburra\Library\Manager\BrowsePagination;
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
    /**
     * browse
     * @param Request $request
     * @Route("/browse/", name="browse")
     * @IsGranted("ROLE_ROUTE")
     */
    public function browse(Request $request, BrowsePagination $pagination)
    {
        if ($request->getMethod() !== 'POST' && $request->getSession()->has('libraryBrowseSearch'))
            $search = $request->getSession()->get('libraryBrowseSearch');
        $search = isset($search) ? $search : new CatalogueSearch();

        $form = $this->createForm(BrowseSearchType::class, $search);

        $form->handleRequest($request);

        if ($form->get('clear')->isClicked()) {
            $search = new CatalogueSearch();
            $form = $this->createForm(BrowseSearchType::class, $search);
        }

        $provider = ProviderFactory::create(LibraryItem::class);
        $content = $provider->getBrowserList($search);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        $request->getSession()->set('libraryBrowseSearch', $search);

        return $this->render('@KookaburraLibrary/browse.html.twig',
            [
                'form' => $form->createView(),
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