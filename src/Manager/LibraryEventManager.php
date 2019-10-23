<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/10/2019
 * Time: 13:53
 */

namespace Kookaburra\Library\Manager;


use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;

/**
 * Class LibraryEventManager
 * @package Kookaburra\Library\Manager
 */
class LibraryEventManager
{
    /**
     * @var LibraryItem
     */
    private $item;

    /**
     * @var $eventType
     */
    private $eventType;

    /**
     * @var LibraryItemEvent
     */
    private $event;

    /**
     * LibraryEventManager constructor.
     * @param LibraryItem $item
     */
    public function __construct(LibraryItem $item, string $eventType)
    {
        $this->item = $item;
        $this->eventType = strtolower($eventType);

        if ($this->isNewEventRequired())
        {
            $this->newEvent();
        }
        if ($this->isExistingEventCompleted())
        {
            dump($this);
        }
    }

    /**
     * @return LibraryItem
     */
    public function getItem(): LibraryItem
    {
        return $this->item;
    }

    /**
     * @return LibraryItemEvent
     */
    public function getEvent(): LibraryItemEvent
    {
        return $this->event =  $this->event ?: new LibraryItemEvent();
    }

    /**
     * isEventRequired
     * @return bool
     */
    private function isNewEventRequired(): bool
    {
        if (! in_array($this->eventType, ['loan','return','action','cancel']))
            return false;
        if ($this->eventType === 'loan')
            return true;
        return false;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * isExistingEventRequired
     * @return bool
     */
    private function isExistingEventRequired(): bool
    {
        return false;
    }

    /**
     * newEvent
     */
    private function newEvent()
    {
        $event = new LibraryItemEvent($this->getItem());
        switch ($this->getEventType()) {
            case 'loan':
                $event->setType('Loan');
                break;
        }
        $em = ProviderFactory::getEntityManager();
        $em->persist($event);
        $em->flush();
    }

    /**
     * isExistingEventCompleted
     * @return bool
     */
    private function isExistingEventCompleted(): bool
    {
        if ($this->getItem()->getLastEvent() && $this->getItem()->getLastEvent()->getTimestampReturn() === null)
            return false;
        return true;
    }
}