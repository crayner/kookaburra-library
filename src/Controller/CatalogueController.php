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
use App\Entity\Space;
use App\Provider\ProviderFactory;
use App\Twig\Sidebar\Photo;
use App\Twig\SidebarContent;
use App\Util\TranslationsHelper;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\CatalogueSearchType;
use Kookaburra\Library\Form\DuplicateCopyIdentifierType;
use Kookaburra\Library\Form\DuplicateItemType;
use Kookaburra\Library\Form\EditType;
use Kookaburra\Library\Manager\CataloguePagination;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\Traits\LibraryControllerTrait;
use Kookaburra\UserAdmin\Util\UserHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CatalogueController
 * @package Kookaburra\Library\Controller
 * @Route("/library", name="library__")
 */
class CatalogueController extends AbstractController
{
    use LibraryControllerTrait;

    /**
     * manageCatalogue
     * @param CataloguePagination $pagination
     * @param Request $request
     * @param LibraryManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/catalogue/manage/", name="manage_catalogue")
     * @Route("/catalogue/manage/", name="status")
     * @Route("/", name="default")
     * @Security("is_granted('ROLE_ROUTE', ['library__manage_catalogue'])")
     */
    public function manageCatalogue(CataloguePagination $pagination, Request $request, LibraryManager $manager)
    {
        if ($request->getMethod() !== 'POST' && $request->getSession()->has('libraryCatalogueSearch'))
            $search = $request->getSession()->get('libraryCatalogueSearch');
        $search = isset($search) ? $search : new CatalogueSearch();

        $form = $this->createForm(CatalogueSearchType::class, $search);

        $form->handleRequest($request);

        if ($form->get('clear')->isClicked()) {
            $search = new CatalogueSearch();
            $form = $this->createForm(CatalogueSearchType::class, $search);
        }

        $provider = ProviderFactory::create(LibraryItem::class);
        $content = $provider->getCatalogueList($search);
        $pagination->setContent($content)->setPageMax(25)
            ->setPaginationScript();

        $request->getSession()->set('libraryCatalogueSearch', $search);
        return $this->render('@KookaburraLibrary/manage_catalogue.html.twig',
            [
                'content' => $content,
                'form' => $form->createView(),
                'count' => $provider->getRepository()->count([]),
            ]
        );
    }

    /**
     * deleteItem
     * @param LibraryItem $item
     * @Security("is_granted('ROLE_ROUTE', ['library__manage_catalogue'])")
     * @Route("/delete/{item}/item/", name="delete_item")
     */
    public function deleteItem(LibraryItem $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();
        $this->addFlash('success', ['id' => 'return.success.0', 'domain' => 'messages']);
        return $this->redirectToRoute('library__manage_catalogue');
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
    public function duplicateItem(LibraryItem $item, Request $request, ContainerManager $manager, LibraryManager $libraryManager, SidebarContent $sidebar)
    {
        $photo = new Photo($item, 'getImageLocation', '240', 'user');
        $photo->setTransDomain('Library')->setTitle('Cover Photo');
        $sidebar->addContent($photo);
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
                        $newItem->setIdentifier(null);
                        $libraryManager->newIdentifier($newItem, true);
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
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
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
    public function duplicateIdentifiers(Request $request, LibraryItem $item, int $copies, ContainerManager $manager, SidebarContent $sidebar)
    {
        $photo = new Photo($item, 'getImageLocation', '240', 'user');
        $photo->setTransDomain('Library')->setTitle('Cover Photo');
        $sidebar->addContent($photo);
        $form = $this->createForm(DuplicateCopyIdentifierType::class, $item,
            [
                'copies' => $copies,
                'action' => $this->generateUrl('library__duplicate_item_copies', ['item' => $item->getId(), 'copies' => $copies]),
            ]
        );

        TranslationsHelper::setDomain('Library');
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
                $this->addFlash('success', TranslationsHelper::translate('Your request was completed successfully. # records were added.', ['count' => $copies], ));
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
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
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
     * @param LibraryManager $libraryManager
     * @param int $item
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/{item}/edit/{tabName}", name="edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function edit(Request $request, ContainerManager $manager, LibraryManager $libraryManager, SidebarContent $sidebar, int $item = 0, string $tabName = 'Catalogue')
    {
        $item = ProviderFactory::getRepository(LibraryItem::class)->find($item) ?: new LibraryItem();
        $photo = new Photo($item, 'getImageLocation', '240', 'user');
        $photo->setTransDomain('Library')->setTitle('Cover Photo');
        $sidebar->addContent($photo);


        if ($request->getContentType() === 'json' && $request->getMethod() === 'POST' && $item->getId() === 0) {
            $content = json_decode($request->getContent(), true);
            $item->setLibrary(ProviderFactory::getRepository(Library::class)->find($content['library']))
                ->setItemType($content['itemType']);
        }
        $manager->setShowSubmitButton(true);
        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName)->setApplication('LibraryApp');
        TranslationsHelper::setDomain('Library');

        $form = $this->createForm(EditType::class, $item,
            [
                'action' => $this->generateUrl('library__edit', ['item' => $item->getId() ?: 0]),
            ]
        );

        if ($request->getContentType() === 'json') {
            $errors = [];
            $status = 'success';
            $content = json_decode($request->getContent(), true);
            $submit = $content['submit_clicked'];
            unset($content['submit_clicked']);
            $item->setLibrary(ProviderFactory::getRepository(Library::class)->find($content['library']));
            $form->submit($content);

            if (!$form->isValid() && $submit !== 'library_type_select') {
                $status = 'error';
                $errors[] = ['class' => 'error', 'message' => TranslationsHelper::translate('return.error.1', [], 'messages')];
            } elseif ($form->isValid()) {
                $item = $libraryManager->handleItem($item, $content);
                $form = $this->createForm(EditType::class, $item,
                    [
                        'action' => $this->generateUrl('library__edit', ['item' => $item->getId() ?: 0]),
                    ]
                );
                $errors[] = ['class' => 'success', 'message' => TranslationsHelper::translate('return.success.0', [], 'messages')];
            } elseif ($submit === 'library_type_select' && $item->getId() === 0) {
                $form = $this->createForm(EditType::class, $item,
                    [
                        'action' => $this->generateUrl('library__edit', ['item' => $item->getId() ?: 0]),
                    ]
                );
            } elseif ($submit === 'library_type_select' && $item->getId() > 0) {
                $item = ProviderFactory::getRepository(LibraryItem::class)->find($item->getId());
                $em = $this->getDoctrine()->getManager();
                $em->refresh($item);
                $item->setItemType($content['itemType'])->setLibrary(ProviderFactory::getRepository(Library::class)->find($content['library']));
                $em->persist($item);
                $em->flush();
                $form = $this->createForm(EditType::class, $item,
                    [
                        'action' => $this->generateUrl('library__edit', ['item' => $item->getId() ?: 0]),
                    ]
                );
            }

            $panel = new Panel('Catalogue', 'Library');
            $container->addForm('single', $form->createView())->addPanel($panel);

            $panel = new Panel('General', 'Library');
            $container->addPanel($panel);

            $panel = new Panel('Specific', 'Library');
            $container->addPanel($panel);

            $manager->addContainer($container)->buildContainers();

            return new JsonResponse(
                [
                    'form' => $manager->getFormFromContainer('formContent', 'single'),
                    'errors' => $errors,
                    'status' => $status,
                ],
                200);
        }

        $panel = new Panel('Catalogue', 'Library');
        $container->addForm('single', $form->createView())->addPanel($panel);

        $panel = new Panel('General', 'Library');
        $container->addPanel($panel);

        $panel = new Panel('Specific', 'Library');
        $container->addPanel($panel);

        $manager->addContainer($container)->buildContainers();

        return $this->render('@KookaburraLibrary/edit_item.html.twig',
            [
                'item' => $item,
            ]
        );
    }

    /**
     * demonstrationLoad
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @IsGranted("ROLE_SYSTEM_ADMIN")
     * @Route("/demonstartion/", name="demonstration")
     */
    public function demonstrationLoad()
    {
        $content = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/migration/demo/libraryDemo.yaml'));

        $em = ProviderFactory::getEntityManager();
        $lib = ProviderFactory::getRepository(Library::class)->find(1) ?: new Library();
        foreach($content['library'] as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $lib->$name($value);
        }

        $space = null;
        try {
            $em->beginTransaction();
            $em->persist($lib);
            foreach($content['item'] as $item) {
                $libItem = new LibraryItem();
                $libItem->setLibrary($lib);
                foreach($item as $name=>$value)
                {
                    switch ($name) {
                        case 'fields':
                            $value = unserialize($value);
                            break;
                        case 'space':
                            $space = $space ?: ProviderFactory::getRepository(Space::class)->findOneBy(['name' => 'Library', 'type' => 'Library']);
                            if (null === $space) {
                                $space = ProviderFactory::getRepository(Space::class)->findOneByType('Library');
                                if (null === $space) {
                                    $space = new Space();
                                    $space->setName('Library')->setType('Library');
                                    $em->persist($space);
                                }
                            }
                            $value = $space;
                            break;
                        case 'createdBy':
                            $value = UserHelper::getCurrentUser();
                            break;
                        case 'statusRecorder':
                            $value = UserHelper::getCurrentUser();
                            break;
                        case 'createdOn':
                            $value = new \DateTimeImmutable($value);
                            break;
                        case 'timestampStatus':
                            $value = new \DateTimeImmutable($value);
                            break;
                    }
                    $name = 'set' . ucfirst($name);
                    $libItem->$name($value);
                }
                $em->persist($libItem);
            }
            $em->flush();
            $em->commit();
        } catch (PDOException $e) {
            $em->rollback();
            $this->addFlash('error',$e->getMessage());
        } catch (DBALException $e) {
            $em->rollback();
            $this->addFlash('error',$e->getMessage());
        }

        return $this->redirectToRoute('library__manage_catalogue');
    }
}