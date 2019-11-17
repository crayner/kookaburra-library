<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 30/10/2019
 * Time: 09:28
 */

namespace Kookaburra\Library\Helper;

use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Manager\LibraryInterface;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\LibraryTrait;
use Kookaburra\SystemAdmin\Notification\EventBuilderProvider;
use Kookaburra\UserAdmin\Util\UserHelper;
use Symfony\Component\Routing\Router;

/**
 * Class ReserveItem
 * @package Kookaburra\Library\Helper
 */
class ReserveItem implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @var LibraryManager
     */
    private $manager;

    /**
     * ReserveItem constructor.
     * @param LibraryItem $item
     * @param LibraryManager $manager
     * @param bool $justReturned
     */
    public function __construct(LibraryItem $item, LibraryManager $manager, bool $justReturned = false)
    {
        $this->manager = $manager;
        $em = ProviderFactory::getEntityManager();
        $action = $item->getReturnAction();
        $now = new DateTimeImmutable();

        if ($justReturned) {
            $item->setStatus('Reserved')
                ->setReturnAction(null)
                ->setResponsibleForStatus($action->getActionBy())
                ->setTimestampStatus($now)
                ->setReturnExpected(new DateTimeImmutable('+ ' . intval($this->getReservePeriod()) . 'days'))
                ->setStatusRecorder(UserHelper::getCurrentUser());
            //  Set event in system for user that reserved.
            $notification = EventBuilderProvider::create('Library', 'Reserved Item Available');

            $notification->addRecipient($item->getResponsibleForStatus())
                ->setText('@KookaburraLibrary/notification/reserve_item.html.twig')
                ->setTextParams(['item' => $item, 'keptTill' => new DateTimeImmutable('+' . intval($this->getReservePeriod()) . ' days'), 'name' => $item->getResponsibleForStatus()->formatName()])
                ->setActionLink($this->getRouter()->generate('library__loan_item', ['item' => $item->getId()], Router::ABSOLUTE_URL))
                ->queueNotifications('Library')
                ->setOption('fromName', $this->getLibraryAdministrator()->formatName())
                ->setOption('fromAddress', $this->getLibraryAdministrator()->getEmail());
        }
        $em->persist($item);
        $em->flush();
    }

    /**
     * getLibraryManager
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager
    {
        return $this->manager;
    }

    /**
     * invoke
     * @param LibraryItem $item
     */
    public function invoke(LibraryItem $item): void
    {

    }
}