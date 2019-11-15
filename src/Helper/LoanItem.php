<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 11:45
 */

namespace Kookaburra\Library\Helper;

use App\Entity\Person;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Manager\LibraryEventManager;
use Kookaburra\Library\Manager\LibraryInterface;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\LibraryTrait;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class LoanItem
 * @package Kookaburra\Library\Helper
 */
class LoanItem implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * LoanItem constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    /**
     * getLibraryManager
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager
    {
        return $this->libraryManager;
    }

    /**
     * loanItem
     * @param LibraryItem $item
     * @return LibraryManager
     * @throws \Exception
     */
    public function loanItem(LibraryItem $item): LoanItem
    {
        if ($item->getStatus() !== 'Available' || !$item->isBorrowable())
            return $this;
        if (! $item->getResponsibleForStatus() instanceof Person)
            return $this;

        $item->setStatus('On Loan');
        $item->setTimestampStatus(new \DateTimeImmutable());
        if (!$item->getReturnExpected() instanceof \DateTimeImmutable)
            $item->setReturnExpected(new \DateTimeImmutable('+'.$item->getLibrary()->getLendingPeriod($this->getBorrowPeriod($item)).' days'));
        $item->setStatusRecorder(UserHelper::getCurrentUser());
        $em = ProviderFactory::getEntityManager();
        $em->persist($item);
        $em->flush();
        $this->getMessageManager()->add('success', 'return.success.0', [], 'messages');
        return $this;
    }

    /**
     * reserveToLoanItem
     * @param LibraryItem $item
     * @return LoanItem
     */
    public function reserveToLoanItem(LibraryItem $item): LoanItem
    {
        if ($item->getStatus() !== 'Reserved' || !$item->isBorrowable())
            return $this;
        $item->setStatus('Available');
        dd($item);

    }
}