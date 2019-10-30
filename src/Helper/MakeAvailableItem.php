<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 30/10/2019
 * Time: 09:03
 */

namespace Kookaburra\Library\Helper;

use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;

/**
 * Class MakeAvailableItem
 * @package Kookaburra\Library\Helper
 */
class MakeAvailableItem
{
    /**
     * MakeAvailableItem constructor.
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function __construct(LibraryItem $item)
    {
        $em = ProviderFactory::getEntityManager();
        $now = new \DateTimeImmutable();
        $item->setReturnAction(null)
            ->setStatus('Available')
            ->setTimestampStatus($now)
            ->setResponsibleForStatus(null);
        $em->persist($item);
        $em->flush();

    }
}