<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 17/11/2019
 * Time: 07:35
 */

namespace Kookaburra\Library\Helper;


use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryInterface;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\LibraryTrait;

/**
 * Class ReturnAction
 * @package Kookaburra\Library\Helper
 */
class ReturnAction implements LibraryInterface
{
    use LibraryTrait;

    /**
     * invoke
     * @param LibraryItem $item
     */
    public function invoke(LibraryItem $item): void
    {
        $ra = $item->getReturnAction();
        if (null === $ra)
            return;

        if ($ra->isValidAction() && $ra->getId() === null)
        {
            ProviderFactory::getEntityManager()->persist($ra);
            ProviderFactory::getEntityManager()->flush();
            return;
        }
        if (!$ra->isValidAction() && $ra->getId() !== null)
        {
            ProviderFactory::getEntityManager()->remove($ra);
            ProviderFactory::getEntityManager()->flush();
        }
        $item->setReturnAction(null);
    }

    /**
     * getLibraryManager
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager
    {
        return null;
    }
}