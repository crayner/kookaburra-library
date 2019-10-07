<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:30
 */

namespace Kookaburra\Library\Entity;

use App\Entity\LibraryType;
use App\Entity\Space;

/**
 * Class CatalogueSearch
 * @package Kookaburra\Library\Entity
 */
class CatalogueSearch
{
    /**
     * @var string
     */
    private $search = '';

    /**
     * @var LibraryType|null
     */
    private $type;

    /**
     * @var Space|null
     */
    private $location;

    /**
     * @var Person|null
     */
    private $person;

    /**
     * @var string
     */
    private $searchFields = '';

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * Search.
     *
     * @param string $search
     * @return CatalogueSearch
     */
    public function setSearch(string $search): CatalogueSearch
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return LibraryType|null
     */
    public function getType(): ?LibraryType
    {
        return $this->type;
    }

    /**
     * Type.
     *
     * @param LibraryType|null $type
     * @return CatalogueSearch
     */
    public function setType(?LibraryType $type): CatalogueSearch
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Space|null
     */
    public function getLocation(): ?Space
    {
        return $this->location;
    }

    /**
     * Location.
     *
     * @param Space|null $location
     * @return CatalogueSearch
     */
    public function setLocation(?Space $location): CatalogueSearch
    {
        $this->location = $location;
        return $this;
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
     * @param Person|null $person
     * @return CatalogueSearch
     */
    public function setPerson(?Person $person): CatalogueSearch
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchFields(): string
    {
        return $this->searchFields;
    }

    /**
     * SearchFields.
     *
     * @param string $searchFields
     * @return CatalogueSearch
     */
    public function setSearchFields(string $searchFields): CatalogueSearch
    {
        $this->searchFields = $searchFields;
        return $this;
    }
}