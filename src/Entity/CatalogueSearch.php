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

use Kookaburra\UserAdmin\Entity\Person;
use App\Entity\Space;
use Kookaburra\Library\Manager\LibraryHelper;
use Kookaburra\Library\Manager\LibraryManager;

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
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $producer = '';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var Space|null
     */
    private $location;

    /**
     * @var null|string
     */
    private $status;

    /**
     * @var Person|null
     */
    private $person;

    /**
     * @var string
     */
    private $searchFields = '';

    /**
     * @var null|string
     */
    private $imageLocation;

    /**
     * @var null|string
     */
    private $imageType;

    /**
     * @var Library|null
     */
    private $library;

    /**
     * CatalogueSearch constructor.
     */
    public function __construct()
    {
        $this->library = LibraryHelper::getCurrentLibrary();
    }


    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search ?: '';
    }

    /**
     * Search.
     *
     * @param null|string $search
     * @return CatalogueSearch
     */
    public function setSearch(?string $search): CatalogueSearch
    {
        $this->search = $search ?: '';
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Title.
     *
     * @param string $title
     * @return CatalogueSearch
     */
    public function setTitle(string $title): CatalogueSearch
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getProducer(): string
    {
        return $this->producer;
    }

    /**
     * Producer.
     *
     * @param string $producer
     * @return CatalogueSearch
     */
    public function setProducer(string $producer): CatalogueSearch
    {
        $this->producer = $producer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * getTypeList
     * @return array
     */
    public static function getTypeList(): array
    {
        return LibraryManager::getItemTypeList();
    }

    /**
     * Type.
     *
     * @param string|null $type
     * @return CatalogueSearch
     */
    public function setType(?string $type): CatalogueSearch
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
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Status.
     *
     * @param string|null $status
     * @return CatalogueSearch
     */
    public function setStatus(?string $status): CatalogueSearch
    {
        $this->status = $status;
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
     * getSearchFields
     * @return string|null
     */
    public function getSearchFields(): string
    {
        return $this->searchFields ?: '';
    }

    /**
     * SearchFields.
     *
     * @param null|string $searchFields
     * @return CatalogueSearch
     */
    public function setSearchFields(?string $searchFields): CatalogueSearch
    {
        $this->searchFields = $searchFields ?: '';
        return $this;
    }

    /**
     * getStatusList
     * @return array
     */
    public static function getStatusList(): array
    {
        return LibraryItem::getStatusList();
    }

    /**
     * @return string|null
     */
    public function getImageLocation(): ?string
    {

        return $this->imageLocation;
    }

    /**
     * ImageLocation.
     *
     * @param string|null $imageLocation
     * @return CatalogueSearch
     */
    public function setImageLocation(?string $imageLocation): CatalogueSearch
    {
        $this->imageLocation = $imageLocation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageType(): ?string
    {
        return $this->imageType;
    }

    /**
     * ImageType.
     *
     * @param string|null $imageType
     * @return CatalogueSearch
     */
    public function setImageType(?string $imageType): CatalogueSearch
    {
        $this->imageType = $imageType;
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
     * @return CatalogueSearch
     */
    public function setLibrary(?Library $library): CatalogueSearch
    {
        $this->library = $library;
        return $this;
    }
}