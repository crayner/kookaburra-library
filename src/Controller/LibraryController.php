<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:06
 */

namespace Kookaburra\Library\Controller;

use App\Entity\LibraryItem;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Form\CatalogueSearchType;
use Kookaburra\Library\Manager\CataloguePagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LibraryController
 * @package Kookaburra\Library\Controller
 * @Route("/library", name="library__")
 */
class LibraryController extends AbstractController
{
    /**
     * manageCatalogue
     * @Route("/catalogue/manage/", name="manage_catalogue")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manageCatalogue(CataloguePagination $pagination, Request $request)
    {
        $search = new CatalogueSearch();
        $form = $this->createForm(CatalogueSearchType::class, $search);

        $form->handleRequest($request);

        $provider = ProviderFactory::create(LibraryItem::class);
        $content = $provider->getCatalogueList($search);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        return $this->render('@KookaburraLibrary/manage_catalogue.html.twig',
            [
                'content' => $content,
            ]
        );
    }

    /**
     * deleteItem
     * @param LibraryItem $item
     * @IsGranted("ROLE_ROUTE")
     * @Route("/delete/{item}/item/", name="delete_item")
     */
    public function deleteItem(LibraryItem $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($item);
        $em->flush();
        $this->addFlash('success', 'Your request was completed successfully.');
        return $this->forward(LibraryController::class.'::manageCatalogue');
    }
}