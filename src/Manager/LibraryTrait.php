<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 11:33
 */

namespace Kookaburra\Library\Manager;

use App\Entity\Person;
use App\Manager\MessageManager;
use Kookaburra\Library\Entity\LibraryItem;
use Symfony\Component\Routing\RouterInterface;

/**
 * Trait LibraryTrait
 * @package Kookaburra\Library\Manager
 */
trait LibraryTrait
{
    /**
     * @return int
     */
    public function getBorrowPeriod(LibraryItem $item): int
    {
        return $this->getLibraryManager()->getBorrowPeriod($item);
    }

    /**
     * @return int
     */
    public function getReservePeriod(): int
    {
        return $this->getLibraryManager()->getReservePeriod();
    }

    /**
     * @return Person
     */
    public function getLibraryAdministrator(): Person
    {
        return $this->getLibraryManager()->getLibraryAdministrator();
    }

    /**
     * getMessageManager
     * @return MessageManager
     */
    public function getMessageManager(): MessageManager
    {
        return $this->getLibraryManager()->getMessageManager();
    }

    /**
     * getRenewalMaximum
     * @return int
     */
    public function getRenewalMaximum(): int
    {
        return $this->getLibraryManager()->getRenewalMaximum();
    }

    /**
     * getRouter
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->getLibraryManager()->getRouter();
    }
}