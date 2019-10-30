<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 30/10/2019
 * Time: 11:09
 */

namespace Kookaburra\Library\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class LibraryItemStatusChangeListener
 * @package Kookaburra\Library\Listener
 */
class LibraryItemStatusChangeListener implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var null|LibraryItem
     */
    private $entity;

    /**
     * getSubscribedEvents
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    /**
     * preUpdate
     * @param LifecycleEventArgs $event
     * @throws \Exception
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->createLibraryItemEvents($event);
    }

    /**
     * createLibraryItemEvents
     * @param LifecycleEventArgs $event
     * @throws \Exception
     */
    public function createLibraryItemEvents(LifecycleEventArgs $event)
    {
        // Stop loops as Item is part of Event.
        if ($this->entity !== null)
            return;

        $entity = $event->getObject();

        if (!$entity instanceof LibraryItem) {
            $this->entity = null;
            return;
        }

        $this->entity = $entity;
        $this->em = $event->getObjectManager();
        $uow = $this->em->getUnitOfWork();
        $changeSet = $uow->getEntitychangeset($this->entity);

        if (isset($changeSet['status']) && $changeSet['status'][0] !== $changeSet['status'][1])
        {
            switch ($changeSet['status'][0])
            {
                case 'On Loan':
                    $this->fromOnLoan($changeSet['status'][1]);
                    break;
                case 'Available':
                    $this->fromAvailable($changeSet['status'][1]);
                    break;
                case 'Repair':
                    $this->fromRepair($changeSet['status'][1]);
                    break;
                case 'Decommissioned':
                    $this->fromDecommissioned($changeSet['status'][1]);
                    break;
                case 'In Use':
                    $this->fromInUse($changeSet['status'][1]);
                    break;
                case 'Lost':
                    $this->fromLost($changeSet['status'][1]);
                    break;
                default:
                    dump($changeSet['status']);
            }
        }

        $this->entity = null;
    }

    /**
     * fromOnLoan
     * @param string $toStatus
     * @throws \Exception
     */
    private function fromOnLoan(string $toStatus)
    {
        $now = new \DateTimeImmutable();
        switch ($toStatus) {
            case 'Available':
                $this->toAvailable();
                break;
            default:
                dump('On Loan => ' . $toStatus);
        }
    }

    /**
     * fromAvailable
     * @param $toStatus
     * @throws \Exception
     */
    private function fromAvailable($toStatus)
    {
        $now = new \DateTimeImmutable();
        switch ($toStatus) {
            case 'On Loan':
                $event = new LibraryItemEvent($this->entity);
                $event->setStatus('On Loan')
                    ->setType('Loan')
                    ->setOutPerson($this->entity->getStatusRecorder())
                    ->setResponsibleForStatus($this->entity->getResponsibleForStatus())
                    ->setTimestampOut($this->entity->getTimestampStatus());
                $this->em->persist($event);
                $this->em->flush();
                break;
            case 'Repair':
                $this->toRepair();
                break;
            case 'Decommissioned':
                $this->toDecommissioned();
                break;
            case 'In Use':
                $this->toInUse();
                break;
            case 'Lost':
                $this->toLost();
                break;
            default:
                dump('Available => ' . $toStatus);
        }
    }

    /**
     * fromAvailable
     * @param $toStatus
     * @throws \Exception
     */
    private function fromDecommissioned($toStatus)
    {
        $now = new \DateTimeImmutable();
        switch ($toStatus) {
            case 'Available':
                $this->toAvailable();
                break;
            default:
                dump('Decommissioned => ' . $toStatus);
        }
    }

    /**
     * fromRepair
     * @param $toStatus
     * @throws \Exception
     */
    private function fromRepair($toStatus)
    {
        $now = new \DateTimeImmutable();
        switch ($toStatus) {
            case 'Available':
                $this->toAvailable();
                break;
            case 'Decommissioned':
                $this->toDecommissioned();
                break;
            default:
                dump('Repair => ' . $toStatus);
        }

    }

    /**
     * toAvailable
     * @throws \Exception
     */
    private function toAvailable()
    {
        dump($this,$this->closeLastEvent());
        if (($event = $this->closeLastEvent()) !== null) {
            $now = new \DateTimeImmutable();
            $event->setStatus('Available')
                ->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn($now);
            $this->em->persist($event);
            $this->em->flush();
        }
    }

    /**
     * toRepair
     */
    private function toRepair()
    {
        if (($event = $this->closeLastEvent()) !== null) {
            $event->setStatus('Repair')
                ->setInPerson($this->entity->getResponsibleForStatus())
                ->setTimestampReturn($this->entity->getTimestampStatus());
            $this->em->persist($event);
        }
        $event = new LibraryItemEvent($this->entity);
        $event->setStatus('Repair')
            ->setType('Repair')
            ->setOutPerson($this->entity->getStatusRecorder())
            ->setResponsibleForStatus($this->entity->getResponsibleForStatus())
            ->setTimestampOut($this->entity->getTimestampStatus());
        $this->em->persist($event);
        $this->em->flush();
    }

    /**
     * closeLastEvent
     * @return LibraryItemEvent|null
     */
    private function closeLastEvent(): ?LibraryItemEvent
    {
        if (null === ($event = $this->entity->getLastEvent()))
            return null;
        if ($event->getTimestampReturn() === null || $event->getInPerson() == null)
            return $event;
        return null;
    }

    /**
     * toDecommission
     */
    private function toDecommissioned()
    {
        if (($event = $this->closeLastEvent()) !== null) {
            $event->setStatus('Decommissioned')
                ->setInPerson($this->entity->getResponsibleForStatus())
                ->setTimestampReturn($this->entity->getTimestampStatus());
            $this->em->persist($event);
        }
        $event = new LibraryItemEvent($this->entity);
        $event->setStatus('Decommissioned')
            ->setType('Decommission')
            ->setOutPerson($this->entity->getStatusRecorder())
            ->setResponsibleForStatus($this->entity->getResponsibleForStatus())
            ->setTimestampOut($this->entity->getTimestampStatus());
        $this->em->persist($event);
        $this->em->flush();
    }

    /**
     * fromRepair
     * @param $toStatus
     * @throws \Exception
     */
    private function fromInUse($toStatus)
    {
        $now = new \DateTimeImmutable();
        switch ($toStatus) {
            case 'Available':
                $this->toAvailable();
                break;
            case 'Decommissioned':
                $this->toDecommissioned();
                break;
            default:
                dump('In Use => ' . $toStatus);
        }
    }

    /**
     * toInUse
     * @throws \Exception
     */
    private function toInUse()
    {
        if (($event = $this->closeLastEvent()) !== null) {
            $event->setStatus('In Use')
                ->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn(new \DateTimeImmutable());
            $this->em->persist($event);
            $this->em->flush();
        }
    }

    /**
     * fromRepair
     * @param $toStatus
     * @throws \Exception
     */
    private function fromLost($toStatus)
    {
        switch ($toStatus) {
            case 'Available':
                $this->toAvailable();
                break;
            default:
                dump('Lost => ' . $toStatus);
        }
    }

    /**
     * toLost
     * @throws \Exception
     */
    private function toLost()
    {
        if (($event = $this->closeLastEvent()) !== null) {
            $event->setStatus('Lost')
                ->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn(new \DateTimeImmutable());
            $this->em->persist($event);
        }
        $event = new LibraryItemEvent($this->entity);
        $event->setStatus('Lost')
            ->setType('Loss')
            ->setOutPerson(UserHelper::getCurrentUser())
            ->setResponsibleForStatus($this->entity->getResponsibleForStatus())
            ->setTimestampOut(new \DateTimeImmutable());
        $this->em->persist($event);
        $this->em->flush();
    }
}