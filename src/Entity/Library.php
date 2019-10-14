<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:14
 */

namespace Kookaburra\Library\Entity;

use App\Entity\Space;
use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Library
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Library",uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),@ORM\UniqueConstraint(name="abbr", columns={"abbr"})}, indexes={@ORM\Index(name="facility", columns={"facility"})})
 */
class Library implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(3) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50, options={"comment": "The library name should be unique."},unique=true)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=6, options={"comment": "The library Abbreviation should be unique."},unique=true)
     */
    private $abbr;

    /**
     * @var Space|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumn(name="facility",referencedColumnName="gibbonSpaceID",nullable=true)
     */
    private $facility;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param int|null $id
     * @return Library
     */
    public function setId(?int $id): Library
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string|null $name
     * @return Library
     */
    public function setName(?string $name): Library
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbr(): ?string
    {
        return $this->abbr;
    }

    /**
     * Abbr.
     *
     * @param string|null $abbr
     * @return Library
     */
    public function setAbbr(?string $abbr): Library
    {
        $this->abbr = $abbr;
        return $this;
    }

    /**
     * @return Space|null
     */
    public function getFacility(): ?Space
    {
        return $this->facility;
    }

    /**
     * Facility.
     *
     * @param Space|null $facility
     * @return Library
     */
    public function setFacility(?Space $facility): Library
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active = $this->active ? true : false;
    }

    /**
     * Active.
     *
     * @param bool $active
     * @return Library
     */
    public function setActive(bool $active): Library
    {
        $this->active = $active ? true : false;
        return $this;
    }
}