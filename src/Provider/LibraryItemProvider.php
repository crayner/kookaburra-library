<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:43
 */

namespace Kookaburra\Library\Provider;

use App\Form\Entity\SearchAny;
use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;

/**
 * Class LibraryItemProvider
 * @package Kookaburra\Library\Provider
 */
class LibraryItemProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = LibraryItem::class;

    /**
     * getCatalogueList
     * @param CatalogueSearch $search
     * @return mixed
     * @throws \Exception
     */
    public function getCatalogueList(CatalogueSearch $search)
    {
        return $this->getRepository()->findBySearch($search);
    }

    /**
     * getBrowserList
     * @param SearchAny $search
     * @return mixed
     * @throws \Exception
     */
    public function getBrowserList(SearchAny $search)
    {
        return $this->getRepository()->findByFullSearch($search);
    }
}