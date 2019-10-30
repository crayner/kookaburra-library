<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 31/10/2019
 * Time: 08:41
 */

namespace Kookaburra\Library\Helper;

use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Manager\LibraryInterface;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\LibraryTrait;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class RenewItem
 * @package Kookaburra\Library\Helper
 */
class RenewItem implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * RenewItem constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    /**
     * renewItem
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function renewItem(LibraryItem $item)
    {
        if ($this->isItemAvailableForRenew($item)) {
            $newReturnDate = $item->getReturnExpected()
                ->add(new \DateInterval('P'.$this->getBorrowPeriod().'D'));
            $item->setReturnExpected($newReturnDate);
            $lastEvent = $item->getLastEvent();
            $now = new \DateTimeImmutable();
            $lastEvent->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn($now);

            $event = new LibraryItemEvent($item);
            $event->setType('Renew Loan')
                ->setOutPerson($lastEvent->getInPerson())
                ->setTimestampOut($now)
            ;

            $item->setLastEvent(null);
            $em = ProviderFactory::getEntityManager();
            $em->persist($event);
            $em->persist($lastEvent);
            $em->persist($item);
            $em->flush();
        }
    }
    /**
     * isItemAvailableForRenew
     * @param LibraryItem $item
     * @return bool
     * @throws \Exception
     */
    private function isItemAvailableForRenew(LibraryItem $item): bool
    {
        $event = $item->getLastEvent();
        if ($event === null || $event->getTimestampReturn() !== null)
        {
            $this->getMessageManager()->add('error', 'The item is not available for renewal.');
            return false;
        }
        if (! in_array($item->getReturnAction(), ['',null])) {
            $this->getMessageManager()->add('warning', 'The return of the item is required for "{action}"', ['{action}' => TranslationsHelper::translate($item->getReturnAction()->getReturnAction())]);
            return false;
        }
        if ($item->getDaysOnLoan() >= $this->getBorrowPeriod() * ($this->getRenewalMaximum() + 1)) {
            $this->getMessageManager()->add('warning', 'This borrower has already exceeded renewal allowances for this library on this item.');
            return false;
        }
        return true;
    }

    /**
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager
    {
        return $this->libraryManager;
    }
}