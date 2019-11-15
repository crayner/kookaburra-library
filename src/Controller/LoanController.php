<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 21/10/2019
 * Time: 09:25
 */

namespace Kookaburra\Library\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Twig\Sidebar\Message;
use App\Twig\Sidebar\Photo;
use App\Twig\SidebarContent;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\RapidLoan;
use Kookaburra\Library\Form\ItemActionType;
use Kookaburra\Library\Form\RapidLoanerType;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\Traits\LibraryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoanController
 * @package Kookaburra\Library\Controller
 * @Route("/library", name="library__")
 */
class LoanController extends AbstractController
{
    use LibraryControllerTrait;

    /**
     * loan
     * @param LibraryItem $item
     * @param SidebarContent $sidebar
     * @param ContainerManager $manager
     * @param LibraryManager $libraryManager
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param FlashBagInterface $flashBag
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Gibbon\Exception
     * @throws \Exception
     * @Route("/loan/{item}/item/", name="loan_item")
     * @IsGranted("ROLE_ROUTE")
     */
    public function loan(LibraryItem $item, SidebarContent $sidebar, ContainerManager $manager, LibraryManager $libraryManager, Request $request, TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        $photo = new Photo($item, 'getImageLocation', '240', 'user');
        $photo->setTransDomain('Library')->setTitle('Cover Photo');
        $sidebar->addContent($photo);

        if ($item->getStatus() === 'Available' && $item->isBorrowable())
            $item->setReturnExpected(new \DateTimeImmutable(date('Y-m-d', strtotime('+'.$item->getLibrary()->getLendingPeriod($libraryManager->getBorrowPeriod($item)).' days' ))));
        elseif (!in_array($item->getStatus(), ['On Loan', 'Reserved']) || !$item->isBorrowable())
            $libraryManager->getMessageManager()->add('warning', 'This item is not available to borrow.', [], 'Library');

        if ($item->getStatus() === 'On Loan' && $item->getReturnExpected() < new \DateTimeImmutable(date('Y-m-d') . ' 00:00:00'))
        {
            $overdue = $item->getReturnExpected() ? $item->getReturnExpected()->diff(new \DateTimeImmutable(date('Y-m-d') . ' 00:00:00')) : null;
            $libraryManager->getMessageManager()->add('error', 'This item is now # days overdue', ['count' => $overdue ? $overdue->days : 0], 'Library');
        }

        $form = $this->createForm(ItemActionType::class, $item, ['action' => $this->generateUrl("library__loan_item", ['item' => $item->getId()])]);

        if ($request->getContentType() === 'json') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            $libraryManager->loanItem($item);
            $libraryManager->returnAction($item);

            $libraryManager->getMessageManager()->pushToFlash($flashBag, $translator);

            return new JsonResponse([
                'redirect' => $this->generateUrl('library__loan_item', ['item' => $item->getId()]),
                'status' => 'redirect',
            ],200);
        }

        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraLibrary/loan_item.html.twig', [
            'item' => $item,
        ]);
    }

    /**
     * return
     * @param LibraryItem $item
     * @param LibraryManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Route("/return/{item}/item/", name="return_item")
     * @Security("is_granted('ROLE_ROUTE', ['library__loan_item'])")
     */
    public function return(LibraryItem $item, LibraryManager $manager)
    {
        $manager->returnItem($item);

        return $this->redirectToRoute('library__loan_item', ['item' => $item->getId()]);
    }

    /**
     * renew
     * @param LibraryItem $item
     * @param LibraryManager $manager
     * @param TranslatorInterface $translator
     * @param FlashBagInterface $flashBag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @Route("/renew/{item}/item/", name="renew_item")
     * @Security("is_granted('ROLE_ROUTE', ['library__loan_item'])")
     */
    public function renew(LibraryItem $item, LibraryManager $manager, TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        $manager->renewItem($item);

        $manager->getMessageManager()->pushToFlash($flashBag, $translator);

        return $this->redirectToRoute('library__loan_item', ['item' => $item->getId()]);
    }

    /**
     * reserveToLoan
     * @param LibraryItem $item
     * @param LibraryManager $libraryManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/reserve/to/loan/{item}/item/", name="reserve_to_loan_item")
     * @Security("is_granted('ROLE_ROUTE', ['library__loan_item'])")
     */
    public function reserveToLoan(LibraryItem $item, LibraryManager $libraryManager)
    {
        $libraryManager->reserveToLoanItem($item);
        return $this->redirectToRoute('library__loan_item', ['item' => $item->getId()]);
    }

    /**
     * quickLoanReturn
     * @param Request $request
     * @Route("/quick/loan/", name="quick_loan")
     */
    public function quickLoanReturn(Request $request, SidebarContent $sidebar, ContainerManager $manager, LibraryManager $libraryManager, TranslatorInterface $translator)
    {
        TranslationsHelper::addTranslation('Loan List', [], 'Library');
        TranslationsHelper::addTranslation('Remove Item', [], 'Library');
        TranslationsHelper::addTranslation('Remove Person', [], 'Library');
        $container = new Container();
        $container->setTarget('formContent')->setApplication('QuickLoanApp');
        $panel = new Panel('single', 'Library');
        $container->addPanel($panel);

        $message = new Message();
        $message->setMessage('This page will attempt to loan any item to any person, based on the item identifier and the person (StudentID) Identifier. Items, if on loan, are instantly returned. Items that are available are added to a loan list until a person is selected, then all items in the list are loaned to the person.  If the person is found first, then a confirmation button must be pressed to loan the items added to the list.', [], 'Library')->setClass('info text-justify space15 no-border');
        $sidebar->addContent($message);

        $loan = new RapidLoan();

        $form = $this->createForm(RapidLoanerType::class, $loan, ['action' => $this->generateUrl('library__quick_loan')]);

        if ($request->getContentType() === 'json') {
            $content = $libraryManager->transformItems(json_decode($request->getContent(), true), $loan);
            $items = clone $loan->getItems();


            if (isset($content['submit_clicked']))
                unset($content['submit_clicked']);
            $form->submit($content);
            $loan->mergeItems($items);
            if ($form->isValid()) {
                $libraryManager->quickLoanSearch($content, $loan);
            }
            $loan->setSearch(null);

            $form = $this->createForm(RapidLoanerType::class, $loan, ['action' => $this->generateUrl('library__quick_loan')]);
            $container->addForm('single', $form->createView());
            $manager->addContainer($container)->buildContainers();
            dump($libraryManager);
            return new JsonResponse([
                'form' => $manager->getFormFromContainer('formContent','single'),
                'status' => 'success',
                'errors' => $libraryManager->getMessageManager()->serialiseTranslatedMessages($translator),
            ],200);
        }

        $container->addForm('single', $form->createView());
        $manager->addContainer($container)->buildContainers();

        return $this->render('@KookaburraLibrary/lending/quick.html.twig');
    }
}