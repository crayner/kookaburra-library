<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 13/11/2019
 * Time: 15:56
 */

namespace Kookaburra\Library\Entity;

use App\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RapidLoan
 * @package Kookaburra\Library\Entity
 */
class RapidLoan
{
    /**
     * @var string|null
     */
    private $search;

    /**
     * @var Person|null
     */
    private $person;

    /**
     * @var ArrayCollection|LibraryItem[]
     */
    private $items;

    /**
     * RapidLoan constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * Search.
     *
     * @param string|null $search
     * @return RapidLoan
     */
    public function setSearch(?string $search): RapidLoan
    {
        $this->search = $search;
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
     * @return RapidLoan
     */
    public function setPerson(?Person $person): RapidLoan
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getItems(): ArrayCollection
    {
        if (null === $this->items)
            $this->items = new ArrayCollection();
        return $this->items;
    }

    /**
     * Items.
     *
     * @param ArrayCollection|LibraryItem[] $items
     * @return RapidLoan
     */
    public function setItems(?ArrayCollection $items): RapidLoan
    {
        $this->items = $items;
        return $this;
    }

    /**
     * mergeItems
     * @param ArrayCollection $items
     * @return RapidLoan
     */
    public function mergeItems(ArrayCollection $items): RapidLoan
    {
        foreach($items as $item)
            $this->addItem($item);
        return $this;
    }

    /**
     * addItem
     * @param LibraryItem $item
     * @return RapidLoan
     */
    public function addItem(LibraryItem $item): RapidLoan
    {
        if ($this->getItems()->contains($item))
            return $this;

        $this->items->add($item);

        return $this;
    }

    /**
     * removeItem
     * @param $item
     * @return RapidLoan
     */
    public function removeItem($item): RapidLoan
    {
        if ($this->getItems()->contains($item))
            $this->items->removeElement($item);

        return $this;
    }

    /**
     * clear
     * @return RapidLoan
     */
    public function clear(): RapidLoan
    {
        return $this->setPerson(null)->setItems(null)->setSearch(null);
    }
}