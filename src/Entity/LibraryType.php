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
namespace Kookaburra\Library\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LibraryType
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryTypeRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryType")
 */
class LibraryType implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", name="gibbonLibraryTypeID", columnDefinition="INT(5) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=30)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $fields;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return LibraryType
     */
    public function setId(?int $id): LibraryType
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
     * @param string|null $name
     * @return LibraryType
     */
    public function setName(?string $name): LibraryType
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * @param string|null $active
     * @return LibraryType
     */
    public function setActive(?string $active): LibraryType
    {
        $this->active = self::checkBoolean($active);
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Fields.
     *
     * @param array $fields
     * @return LibraryType
     */
    public function setFields(array $fields): LibraryType
    {
        $this->fields = $fields;
        return $this;
    }
}