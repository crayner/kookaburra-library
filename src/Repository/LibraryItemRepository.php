<?php
/**
 * Created by PhpStorm.
 *
 * Gibbon-Responsive
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\Library\Repository;

use App\Entity\Person;
use App\Entity\Space;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryType;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class LibraryItemRepository
 * @package Kookaburra\Library\Repository
 */
class LibraryItemRepository extends ServiceEntityRepository
{
    /**
     * ApplicationFormRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
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
    public function findBySearch(CatalogueSearch $search)
    {
        $query = $this->createQueryBuilder('li')
            ->where('li.identifier LIKE :search OR li.name LIKE :search OR li.producer LIKE :search')
            ->setParameter('search', '%'.$search->getSearch().'%')
            ->andWhere('(li.fields LIKE :searchField)')
            ->setParameter('searchField', '%'.$search->getSearchFields().'%');

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

        return $query->orderBy('li.identifier','ASC')->getQuery()
            ->getResult();
    }
}
