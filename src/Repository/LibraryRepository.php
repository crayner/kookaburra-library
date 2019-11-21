<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:20
 */

namespace Kookaburra\Library\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Kookaburra\Library\Entity\Library;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class LibraryRepository
 * @package Kookaburra\Library\Repository
 */
class LibraryRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Library::class);
    }
}