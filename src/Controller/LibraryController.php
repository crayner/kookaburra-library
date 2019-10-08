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

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\CatalogueSearchType;
use Kookaburra\Library\Form\DuplicateCopyIdentifierType;
use Kookaburra\Library\Form\DuplicateItemType;
use Kookaburra\Library\Form\EditType;
use Kookaburra\Library\Manager\CataloguePagination;
use Kookaburra\Library\Manager\LibraryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * duplicateItem
     * @param LibraryItem $item
     * @param Request $request
     * @param ContainerManager $manager
     * @param LibraryManager $libraryManager
     * @return \Symfony\Component\HttpFoundation\Response
     * @IsGranted("ROLE_ROUTE")
     * @Route("/duplicate/{item}/item/", name="duplicate_item")
     */
    public function duplicateItem(LibraryItem $item, Request $request, ContainerManager $manager, LibraryManager $libraryManager)
    {
        $form = $this->createForm(DuplicateItemType::class, $item,
            [
                'action' => $this->generateUrl('library__duplicate_item', ['item' => $item->getId()]),
            ]
        );

        if ($request->getContentType() === 'json') {
            $errors = [];
            $status = 'success';
            $content = json_decode($request->getContent(), true);

            $form->submit($content);
            if ($form->isSubmitted() && $form->isValid()) {
                $copies = $form->get('copies')->getData();
                if ($libraryManager->isGenerateIdentifier())
                {
                    $em = $this->getDoctrine()->getManager();
                    for($x=1; $x<=$copies; $x++)
                    {
                        $newItem = clone $item;
                        $libraryManager->newIdentifier($newItem);
                        $em->persist($newItem);
                    }
                    $em->flush();
                } else {
                    $manager->singlePanel($form->createView());
                    return new JsonResponse(
                        [
                            'form' => $manager->getFormFromContainer('formContent', 'single'),
                            'errors' => $errors,
                            'status' => 'redirect',
                            'redirect' => $this->generateUrl('library__duplicate_item_copies', ['item' => $item->getId(), 'copies' => $copies]),
                        ],
                        200);

                }
                $errors[] = ['class' => 'success', 'message' => TranslationsHelper::translate('Your request was completed successfully. # records were added.', ['count' => $form->get('copies')->getData()])];
            } else {
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed because your inputs were invalid.')];
                $status = 'error';
            }

            $manager->singlePanel($form->createView());
            return new JsonResponse(
                [
                    'form' => $manager->getFormFromContainer('formContent', 'single'),
                    'errors' => $errors,
                    'status' => $status,
                ],
                200);
        }

        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraLibrary/duplicate_item.html.twig');
    }

    /**
     * duplicateIDList
     * @param Request $request
     * @param LibraryItem $item
     * @param int $copies
     * @Security("is_granted('ROLE_ROUTE', ['library__duplicate_item'])")
     * @Route("/duplicate/{item}/item/{copies}/copies/", name="duplicate_item_copies")
     */
    public function duplicateIdentifiers(Request $request, LibraryItem $item, int $copies, ContainerManager $manager)
    {
        $form = $this->createForm(DuplicateCopyIdentifierType::class, $item,
            [
                'copies' => $copies,
                'action' => $this->generateUrl('library__duplicate_item_copies', ['item' => $item->getId(), 'copies' => $copies]),
            ]
        );

        if ($request->getContentType() === 'json') {
            $errors = [];
            $status = 'success';
            $content = json_decode($request->getContent(), true);

            $form->submit($content);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                for($x=1; $x<=$copies; $x++)
                {
                    $identifier = $form->get('identifier'.$x)->getData();
                    $newItem = clone $item;
                    $newItem->setIdentifier($identifier);
                    $em->persist($newItem);
                }
                $em->flush();

                $manager->singlePanel($form->createView());
                $this->addFlash('success', TranslationsHelper::translate('Your request was completed successfully. # records were added.', ['count' => $copies]));
                return new JsonResponse(
                    [
                        'form' => $manager->getFormFromContainer('formContent', 'single'),
                        'errors' => $errors,
                        'status' => 'redirect',
                        'redirect' => $this->generateUrl('library__duplicate_item', ['item' => $item->getId()]),
                    ],
                    200);
            } else {
                $manager->singlePanel($form->createView());
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed because your inputs were invalid.')];
                return new JsonResponse(
                    [
                        'form' => $manager->getFormFromContainer('formContent', 'single'),
                        'errors' => $errors,
                        'status' => 'error',
                    ],
                    200);
            }
        }

        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraLibrary/duplicate_item.html.twig');
    }

    /**
     * edit
     * @param Request $request
     * @param ContainerManager $manager
     * @param int $item
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/{item}/edit/{tabName}", name="edit")
     */
    public function edit(Request $request, ContainerManager $manager, int $item = 0, string $tabName = 'Catalogue')
    {
        $item = ProviderFactory::getRepository(LibraryItem::class)->find($item) ?: new LibraryItem();
        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName)->setApplication($tabName);

        $form = $this->createForm(EditType::class, $item,
            [
                'action' => $this->generateUrl('library__edit', ['item' => $item->getId() ?: 0]),
            ]
        );

        if ($request->getContentType() === 'json') {
            $errors = [];
            $status = 'success';
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            $panel = new Panel('Catalogue');
            $container->addForm('Catalogue', $form->createView())->addPanel($panel);

            $panel = new Panel('General');
            $container->addForm('General', $form->createView())->addPanel($panel);

            $panel = new Panel('Specific');
            $container->addForm('Specific', $form->createView())->addPanel($panel);

            $manager->addContainer($container)->buildContainers();
            return new JsonResponse(
                [
                    'form' => $manager->getFormFromContainer('formContent', $tabName),
                    'errors' => $errors,
                    'status' => 'success',
                ],
                200);
        }

        $panel = new Panel('Catalogue');
        $container->addForm('single', $form->createView())->addPanel($panel);

        $panel = new Panel('General');
        $container->addForm('single', $form->createView())->addPanel($panel);

        $panel = new Panel('Specific');
        $container->addForm('single', $form->createView())->addPanel($panel);

        $manager->addContainer($container)->buildContainers();

        return $this->render('@KookaburraLibrary/edit_item.html.twig',
            [
                'item' => $item,
            ]
        );
    }
}