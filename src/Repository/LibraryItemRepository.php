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

use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\SchoolAdmin\Entity\Facility;
use App\Form\Entity\SearchAny;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\NonUniqueResultException;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\IgnoreStatus;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryHelper;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class LibraryItemRepository
 * @package Kookaburra\Library\Repository
 */
class LibraryItemRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibraryItem::class);
    }

    /**
     * findAllIn
     * @param $items
     * Array of item ID's
     * @return array
     */
    public function findAllIn($items): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.id IN (:items)')
            ->setParameter('items', $items, Connection::PARAM_INT_ARRAY)
            ->orderBy('s.name')
            ->getQuery()
            ->getResult();
    }

    /**
     * findBySearch
     * @param CatalogueSearch $search
     * @return mixed
     */
    public function findBySearch(CatalogueSearch $search, bool $asArray = false)
    {
        $query = $this->createQueryBuilder('li')
            ->where('li.identifier LIKE :search OR li.name LIKE :search OR li.producer LIKE :search')
            ->setParameter('search', '%'.$search->getSearch().'%')
            ->andWhere('(li.fields LIKE :searchField)')
            ->setParameter('searchField', '%'.$search->getSearchFields().'%');

        if ($asArray) {
            $query->leftJoin('li.library', 'l')
                ->leftJoin('li.space', 's')
                ->leftJoin('li.ownership', 'o')
                ->select(['l AS Library', 'li As LibraryItem', 's AS Space', 'o as Owner']);
        }

        if (null !== $search->getType())
            $query->andWhere('li.libraryType = :libraryType')
                ->setParameter('libraryType', $search->getType());

        if (null !== $search->getStatus())
            $query->andWhere('li.status = :status')
                ->setParameter('status', $search->getStatus());

        if ($search->getLocation() instanceof Space)
            $query->andWhere('li.space = :space')
                ->setParameter('space', $search->getLocation());

        if ($search->getPerson() instanceof Person)
            $query->andWhere('li.ownership = :person')
                ->setParameter('person', $search->getPerson());

        if ($search->getLibrary() instanceof Library)
            $query->andWhere('li.library = :library')
                ->setParameter('library', $search->getLibrary());

        return $query->orderBy('li.identifier','ASC')->getQuery()
            ->getResult();
    }

    /**
     * findLatestCreated
     * @param Library|null $library
     * @return array
     */
    public function findLatestCreated(?Library $library = null): array
    {
        $library = $library ?: LibraryHelper::getCurrentLibrary();
        return $this->createQueryBuilder('li')
            ->select(['li.name', 'li.producer'])
            ->where('li.library = :library')
            ->andWhere('li.borrowable = :yes')
            ->orderBy('li.createdOn', 'DESC')
            ->setMaxResults(5)
            ->setParameter('library', $library)
            ->setParameter('yes', 'Y')
            ->getQuery()
            ->getResult();
    }

    /**
     * findByFullSearch
     * @param SearchAny $search
     * @return array
     */
    public function findByFullSearch(SearchAny $search): array
    {
        $metaData  = $this->getEntityManager()->getClassMetadata(LibraryItem::class);

        $alias = "li";

        $qb = $this->createQueryBuilder($alias);

        foreach ($metaData->getFieldNames() as $name) {
            $meta = $metaData->getFieldMapping($name);
            if (in_array($meta['type'], ['string','text', 'array', 'simple_array', 'datetime_immutable', 'date_immutable', 'integer', 'decimal'])){
                $qb->orWhere($qb->expr()->like($alias . '.' . $name, ":search" ));
            }
        }
        $qb->setParameter('search', '%'.$search->getSearch().'%');
        if (LibraryHelper::getCurrentLibrary() instanceof Library) {
            $qb->andWhere($alias . '.library = :library')
                ->setParameter('library', LibraryHelper::getCurrentLibrary());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * findOverdue
     * @param IgnoreStatus $status
     * @return array
     * @throws \Exception
     */
    public function findOverdue(IgnoreStatus $status): array
    {
        $library = LibraryHelper::getCurrentLibrary();
        $today = new \DateTimeImmutable(date('Y-m-d'));

        $query = $this->createQueryBuilder('li')
            ->select(['p','li'])
            ->join('li.responsibleForStatus', 'p')
            ->where('li.status = :status')
            ->setParameter('status', 'On Loan')
            ->andWhere('li.borrowable = :true')
            ->setParameter('true', 'Y')
            ->andWhere('li.returnExpected < :today')
            ->setParameter('today', $today)
            ->orderBy('p.surname', 'ASC')
            ->addOrderBy('p.preferredName', 'ASC')
        ;

        if (!$status->isStatus())
            $query->andWhere('p.status = :full')
                ->setParameter('full', 'Full');

        return $query->getQuery()
            ->getResult();
    }

    /**
     * findOneUsingQuickSearch
     * @param string $search
     * @return mixed
     */
    public function findOneUsingQuickSearch(string $search)
    {
        try {
            return $this->createQueryBuilder('li')
                ->where('li.identifier = :search')
                ->setParameter('search', $search)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * countOnLoanToPerson
     * @param Person $person
     * @return int
     */
    public function countOnLoanToPerson(Person $person): int
    {
        try {
            return intval($this->createQueryBuilder('li')
                ->where('li.status = :status')
                ->setParameter('status', 'On Loan')
                ->andWhere('li.responsibleForStatus = :person')
                ->setParameter('person', $person)
                ->select('COUNT(li.id)')
                ->getQuery()
                ->getSingleScalarResult());
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }
}
