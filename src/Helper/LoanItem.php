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
     */
    public function invoke(LibraryItem $item): void
    {
        if ($item->getStatus() !== 'Available' || !$item->isBorrowable())
            return ;
        if (! $item->getResponsibleForStatus() instanceof Person)
            return ;
        if (!$this->getLibraryManager()->canBorrow($item->getResponsibleForStatus()))
        {
            $this->getMessageManager()->add('warning', 'return.warning.0', ['{person}' => $item->getResponsibleForStatus()->formatName(['informal' => true]), '{name}' => $item->getName()], 'Library');
            return ;
        }

        $item->setStatus('On Loan');
        $item->setTimestampStatus(new \DateTimeImmutable());
        $ra = new ReturnAction();
        $ra->invoke($item);
        if (!$item->getReturnExpected() instanceof \DateTimeImmutable)
            $item->setReturnExpected(new \DateTimeImmutable('+'. intval($item->getLibrary()->getLendingPeriod($this->getBorrowPeriod($item))).' days'));
        $item->setStatusRecorder(UserHelper::getCurrentUser());
        $em = ProviderFactory::getEntityManager();
        $em->persist($item);
        $em->flush();
        $this->getMessageManager()->add('success', 'return.success.0', ['{name}' => $item->getName(), '{person}' => $item->getResponsibleForStatus()->formatName(['informal' => true])], 'Library');
        return ;
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