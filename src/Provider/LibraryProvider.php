<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 18/11/2019
 * Time: 11:57
 */

namespace Kookaburra\Library\Provider;


use App\Entity\Person;
use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Kookaburra\Library\Entity\Library;

class LibraryProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = Library::class;

    /**
     * findPeopleFormIdentifierReport
     * @param $form
     * @return mixed
     */
    public function findPeopleFormIdentifierReport($form)
    {
        return $this->getRepository(Person::class)->createQueryBuilder('p')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}