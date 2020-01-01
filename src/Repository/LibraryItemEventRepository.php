<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\Library\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Kookaburra\Library\Entity\BorrowerSearch;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\Library\Manager\LibraryHelper;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class LibraryItemEventRepository
 * @package Kookaburra\Library\Repository
 */
class LibraryItemEventRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibraryItemEvent::class);
    }

    /**
     * findMonthlyTop5Loan
     * @return array
     * @throws \Exception
     */
    public function findMonthlyTop5Loan(?Library $library = null): array
    {
        $library = $library ?: LibraryHelper::getCurrentLibrary();
        return $this->createQueryBuilder('lie')
            ->where('lie.timestampOut > :theMonth')
            ->andWhere('lie.type = :loan')
            ->andWhere('li.library = :library')
            ->setParameters(['loan' => 'Loan', 'theMonth' => new \DateTimeImmutable('-1 Month'), 'library' => $library])
            ->groupBy('lie.libraryItem')
            ->join('lie.libraryItem', 'li')
            ->select(['li.name AS name', 'COUNT(lie.id) AS loans', 'li.producer AS producer'])
            ->orderBy('loans', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * findBorrowerRecords
     * @param BorrowerSearch $search
     * @return array
     */
    public function findBorrowerRecords(BorrowerSearch $search): array
    {
        return $this->createQueryBuilder('lie')
            ->select(['lie','li','p'])
            ->where('li.library = :library')
            ->andWhere("lie.responsibleForStatus = :person")
            ->join('lie.libraryItem', 'li')
            ->join('lie.responsibleForStatus', 'p')
            ->setParameter('person', $search->getPerson())
            ->setParameter('library', $search->getLibrary())
            ->orderBy('lie.timestampOut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
