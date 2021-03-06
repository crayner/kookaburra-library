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
 * Date: 28/10/2019
 * Time: 14:08
 */

namespace Kookaburra\Library\Entity;

use Kookaburra\UserAdmin\Entity\Person;
use App\Manager\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Kookaburra\UserAdmin\Util\UserHelper;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LibraryReturnAction
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryReturnActionRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryReturnAction",
 *     indexes={@ORM\Index(name="action_by", columns={"action_by"}),
 *     @ORM\Index(name="created_by", columns={"created_by"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="item", columns={"library_item"})})
 * @ORM\HasLifecycleCallbacks()
 */
class LibraryReturnAction implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var LibraryItem|null
     * @ORM\OneToOne(targetEntity="LibraryItem", inversedBy="returnAction")
     * @ORM\JoinColumn(name="library_item", referencedColumnName="id",nullable=false,unique=true)
     */
    private $item;

    /**
     * @var string|null
     * @ORM\Column(name="return_action", length=16, options={"comment": "What to do when the item is returned?"}, nullable=false)
     * @Assert\Choice(callback="getReturnActionList")
     */
    private $returnAction;

    /**
     * @var array
     */
    private static $returnActionList = ['Make Available','Decommission','Repair','Reserve'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="action_by", referencedColumnName="id", nullable=false)
     */
    private $actionBy;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(name="created_on", type="datetime_immutable")
     */
    private $createdOn;

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
     * @return LibraryReturnAction
     */
    public function setId(?int $id): LibraryReturnAction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getItem
     * @return LibraryItem|null
     */
    public function getItem(): ?LibraryItem
    {
        return $this->item;
    }

    /**
     * Item.
     *
     * @param LibraryItem|null $item
     * @return LibraryReturnAction
     */
    public function setItem(?LibraryItem $item, bool $swap = true): LibraryReturnAction
    {
        if ($swap && $item)
            $item->setReturnAction($this, false);
        $this->item = $item;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnAction(): ?string
    {
        return $this->returnAction;
    }

    /**
     * ReturnItem.
     *
     * @param string|null $returnAction
     * @return LibraryReturnAction
     */
    public function setReturnAction(?string $returnAction): LibraryReturnAction
    {
        $this->returnAction = $returnAction;
        return $this;
    }

    /**
     * @return array
     */
    public static function getReturnActionList(): array
    {
        return self::$returnActionList;
    }

    /**
     * @return Person|null
     */
    public function getActionBy(): ?Person
    {
        return $this->actionBy;
    }

    /**
     * ActionBy.
     *
     * @param Person|null $actionBy
     * @return LibraryReturnAction
     */
    public function setActionBy(?Person $actionBy): LibraryReturnAction
    {
        $this->actionBy = $actionBy;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreatedBy(): ?Person
    {
        return $this->createdBy;
    }

    /**
     * CreatedBy.
     *
     * @param Person|null $createdBy
     * @return LibraryReturnAction
     */
    public function setCreatedBy(?Person $createdBy): LibraryReturnAction
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    /**
     * CreatedOn.
     *
     * @param \DateTimeImmutable|null $createdOn
     * @return LibraryReturnAction
     */
    public function setCreatedOn(?\DateTimeImmutable $createdOn): LibraryReturnAction
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    /**
     * createCreatedDetails
     * @return LibraryReturnAction
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function createCreatedDetails(): LibraryReturnAction
    {
        return $this->setCreatedBy(UserHelper::getCurrentUser())
            ->setCreatedOn(new \DateTimeImmutable());
    }

    /**
     * isValidAction
     * @return bool
     */
    public function isValidAction(): bool
    {
        return in_array($this->getReturnAction(), self::getReturnActionList()) && $this->getActionBy() instanceof Person && $this->getItem() instanceof LibraryItem;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}