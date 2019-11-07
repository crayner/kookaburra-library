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

use App\Container\ContainerManager;
use App\Twig\Sidebar;
use App\Util\GlobalHelper;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Form\ItemActionType;
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
     * @param Sidebar $sidebar
     * @param ContainerManager $manager
     * @param LibraryManager $libraryManager
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param FlashBagInterface $flashBag
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Gibbon\Exception
     * @Route("/loan/{item}/item/", name="loan_item")
     * @IsGranted("ROLE_ROUTE")
     */
    public function loan(LibraryItem $item, Sidebar $sidebar, ContainerManager $manager, LibraryManager $libraryManager, Request $request, TranslatorInterface $translator, FlashBagInterface $flashBag)
    {
        if (null !== $item->getImageLocation())
            $sidebar->addExtra('image', ['asset' => GlobalHelper::localAssetorURL($item->getImageLocation()), 'class' => 'user max200']);

        if ($item->getStatus() === 'Available' && $item->isBorrowable())
            $item->setReturnExpected(new \DateTimeImmutable(date('Y-m-d', strtotime('+'.$item->getLibrary()->getLendingPeriod($libraryManager->getBorrowPeriod($item)).' days' ))));
        elseif (!in_array($item->getStatus(), ['On Loan', 'Reserved']) || !$item->isBorrowable())
            $libraryManager->getMessageManager()->add('warning', 'This item is not available to borrow.', [], 'Library');

        if ($item->getStatus() === 'On Loan' && $item->getReturnExpected() < new \DateTimeImmutable(date('Y-m-d') . ' 00:00:00'))
        {
            $overdue = $item->getReturnExpected()->diff(new \DateTimeImmutable(date('Y-m-d') . ' 00:00:00'));
            $libraryManager->getMessageManager()->add('error', 'This item is now # days overdue', ['count' => $overdue['days']], 'Library');
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
}