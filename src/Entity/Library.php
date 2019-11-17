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

use App\Entity\Department;
use App\Entity\Space;
use App\Manager\EntityInterface;
use App\Validator as Validator;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Library
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="Library",uniqueConstraints={@ORM\UniqueConstraint(name="name", columns={"name"}),@ORM\UniqueConstraint(name="abbr", columns={"abbr"})},indexes={@ORM\Index(name="facility", columns={"facility"}), @ORM\Index(name="department", columns="department") })
 */
class Library implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(3) UNSIGNED ZEROFILL")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50, options={"comment": "The library name should be unique."},unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max = 50)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=6, options={"comment": "The library Abbreviation should be unique."},unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max = 6)
     */
    private $abbr;

    /**
     * @var Space|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumn(name="facility",referencedColumnName="gibbonSpaceID",nullable=true)
     */
    private $facility;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Department")
     * @ORM\JoinColumn(name="department",referencedColumnName="gibbonDepartmentID",nullable=true)
     */
    private $department;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @var integer
     * @ORM\Column(type="smallint",options={"comment": "Lending period default for this library in days."})
     * @Assert\Range(min=1,max=365)
     */
    private $lendingPeriod = 14;

    /**
     * @var string
     * @ORM\Column(length=32, options={"default": "white"}, name="bg_colour")
     * @Validator\Colour()
     */
    private $bgColour = 'white';

    /**
     * @var File|null
     * @ORM\Column(length=191, name="bg_image", nullable=true)
     */
    private $bgImage;

    /**
     * @var integer|null
     * @ORM\Column(type="integer", nullable=true, columnDefinition="INT(3) UNSIGNED")
     * @Assert\Range(max = 99)
     */
    private $borrowLimit;

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

    /**
     * @return null|int
     */
    public function getLendingPeriod(?int $default = 14): ?int
    {
        return $this->lendingPeriod ?: $default;
    }

    /**
     * LendingPeriod.
     *
     * @param int $lendingPeriod
     * @return Library
     */
    public function setLendingPeriod(int $lendingPeriod): Library
    {
        $this->lendingPeriod = $lendingPeriod;
        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * Department.
     *
     * @param Department|null $department
     * @return Library
     */
    public function setDepartment(?Department $department): Library
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string
     */
    public function getBgColour(): string
    {
        return $this->bgColour;
    }

    /**
     * BgColour.
     *
     * @param string $bgColour
     * @return Library
     */
    public function setBgColour(string $bgColour): Library
    {
        $this->bgColour = $bgColour;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getBgImage(): ?string
    {
        return $this->bgImage;
    }

    /**
     * BgImage.
     *
     * @param null|string $bgImage
     * @return Library
     */
    public function setBgImage(?string $bgImage): Library
    {
        $this->bgImage = $bgImage;
        return $this;
    }

    /**
     * @return int
     */
    public function getBorrowLimit(): int
    {
        return intval($this->borrowLimit);
    }

    /**
     * BorrowLimit.
     *
     * @param int|null $borrowLimit
     * @return Library
     */
    public function setBorrowLimit(?int $borrowLimit): Library
    {
        $this->borrowLimit = $borrowLimit;
        return $this;
    }
}