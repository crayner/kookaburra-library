<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 30/10/2019
 * Time: 09:16
 */

namespace Kookaburra\Library\Helper;

use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class DecommissionItem
 * @package Kookaburra\Library\Helper
 */
class DecommissionItem
{
    /**
     * DecommissionItem constructor.
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function __construct(LibraryItem $item)
    {
        $em = ProviderFactory::getEntityManager();
        $action = $item->getReturnAction();
        $now = new \DateTimeImmutable();
        $item->setStatus('Decommissioned')
            ->setTimestampStatus($now)
            ->setStatusRecorder(UserHelper::getCurrentUser())
            ->setReturnAction(null)
            ->setResponsibleForStatus($action->getActionBy());
        $event = $item->getLastEvent();
        if ($event !== null) {
            $event->setStatus('Decommissioned')
                ->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn($now);
            $em->persist($event);
        }
        $newEvent = new LibraryItemEvent($item);
        $newEvent->setOutPerson(UserHelper::getCurrentUser())
            ->setTimestampOut($now)
            ->setStatus('Decommissioned')
            ->setType('DecommissionItem')
            ->setResponsibleForStatus($action->getActionBy())
        ;
        $em->persist($newEvent);
        $em->persist($item);
        $em->flush();
    }
}