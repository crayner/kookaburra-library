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
use App\Form\Entity\SearchAny;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryHelper;
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
}
