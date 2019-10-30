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
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class LibraryReturnActionRepository
 * @package Kookaburra\LibraryReturnAction\Repository
 */
class LibraryReturnActionRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LibraryReturnAction::class);
    }
}