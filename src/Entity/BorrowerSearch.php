<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:30
 */

namespace Kookaburra\Library\Entity;

use Kookaburra\UserAdmin\Entity\Person;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Manager\LibraryHelper;

/**
 * Class BorrowerSearch
 * @package Kookaburra\Library\Entity
 */
class BorrowerSearch
{
    /**
     * @var Person|null
     */
    private $person;

    /**
     * BorrowerSearch constructor.
     */
    public function __construct()
    {
        $this->library = LibraryHelper::getCurrentLibrary();
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * Person.
     *
     * @param Person|int|null $person
     * @return BorrowerSearch
     */
    public function setPerson($person): BorrowerSearch
    {
        if (is_int($person))
            $person = ProviderFactory::getRepository(Person::class)->find($person);
        $this->person = $person;
        return $this;
    }

    /**
     * @return Library|null
     */
    public function getLibrary(): ?Library
    {
        return $this->library;
    }

    /**
     * Library.
     *
     * @param Library|null $library
     * @return BorrowerSearch
     */
    public function setLibrary(?Library $library): BorrowerSearch
    {
        $this->library = $library;
        return $this;
    }
}