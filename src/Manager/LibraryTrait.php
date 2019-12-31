<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 11:33
 */

namespace Kookaburra\Library\Manager;

use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\MessageManager;
use Kookaburra\Library\Entity\LibraryItem;
use Symfony\Component\Routing\RouterInterface;

/**
 * Traits LibraryTrait
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