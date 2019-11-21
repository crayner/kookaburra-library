<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 13:20
 */

namespace Kookaburra\Library\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Kookaburra\Library\Entity\LibraryReturnAction;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class LibraryReturnActionRepository
 * @package Kookaburra\LibraryReturnAction\Repository
 */
class LibraryReturnActionRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibraryReturnAction::class);
    }
}