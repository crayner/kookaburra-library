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

use App\Entity\Person;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LibraryItemEvent
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryItemEventRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryItemEvent")
 */
class LibraryItemEvent
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="bigint", name="gibbonLibraryItemEventID", columnDefinition="INT(14) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var LibraryItem|null
     * @ORM\ManyToOne(targetEntity="LibraryItem", inversedBy="events")
     * @ORM\JoinColumn(name="gibbonLibraryItemID", referencedColumnName="gibbonLibraryItemID", nullable=false)
     */
    private $libraryItem;

    /**
     * @var string|null
     * @ORM\Column(name="type", length=12, options={"comment": "This is maintained even after the item is returned, so we know what type of event it was.", "default": "Other"})
     */
    private $type = 'Other';

    /**
     * @var array
     */
    private static $typeList = ['Decommission','Loss','Loan','Repair','Reserve','Other'];

    /**
     * @var string|null
     * @ORM\Column(name="status", length=16, options={"default": "Available"})
     */
    private $status = 'Available';

    /**
     * @var array
     */
    private static $statusList = ['Available','Decommissioned','Lost','On Loan','Repair','Reserved','Returned'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDStatusResponsible", referencedColumnName="gibbonPersonID", nullable=true)
     * The person who was responsible for the event.
     */
    private $responsibleForStatus;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDOut", referencedColumnName="gibbonPersonID", nullable=true)
     */
    private $outPerson;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="timestampOut", type="datetime", nullable=true, options={"comment": "The time the event was recorded"})
     */
    private $timestampOut;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="returnExpected", type="date", nullable=true, options={"comment": "The time when the event expires."})
     */
    private $returnExpected;

    /**
     * @var string|null
     * @ORM\Column(name="returnAction", length=16, nullable=true, options={"comment": "What to do when the item is returned?"})
     */
    private $returnAction;

    /**
     * @var array
     */
    private static $returnActionList = ['Make Available','Decommission','Repair','Reserve'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDReturnAction", referencedColumnName="gibbonPersonID", nullable=true)
     */
    private $returnActionPerson;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="timestampReturn", type="datetime", nullable=true)
     */
    private $timestampReturn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDIn", referencedColumnName="gibbonPersonID", nullable=true)
     */
    private $inPerson;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return LibraryItemEvent
     */
    public function setId(?int $id): LibraryItemEvent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return LibraryItem|null
     */
    public function getLibraryItem(): ?LibraryItem
    {
        return $this->libraryItem;
    }

    /**
     * @param LibraryItem|null $libraryItem
     * @return LibraryItemEvent
     */
    public function setLibraryItem(?LibraryItem $libraryItem): LibraryItemEvent
    {
        $this->libraryItem = $libraryItem;
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
     * @param string|null $type
     * @return LibraryItemEvent
     */
    public function setType(?string $type): LibraryItemEvent
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Other';
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
     * @param string|null $status
     * @return LibraryItemEvent
     */
    public function setStatus(?string $status): LibraryItemEvent
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Available';
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getResponsibleForStatus(): ?Person
    {
        return $this->responsibleForStatus;
    }

    /**
     * @param Person|null $responsibleForStatus
     * @return LibraryItemEvent
     */
    public function setResponsibleForStatus(?Person $responsibleForStatus): LibraryItemEvent
    {
        $this->responsibleForStatus = $responsibleForStatus;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getOutPerson(): ?Person
    {
        return $this->outPerson;
    }

    /**
     * @param Person|null $outPerson
     * @return LibraryItemEvent
     */
    public function setOutPerson(?Person $outPerson): LibraryItemEvent
    {
        $this->outPerson = $outPerson;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestampOut(): ?\DateTime
    {
        return $this->timestampOut;
    }

    /**
     * @param \DateTime|null $timestampOut
     * @return LibraryItemEvent
     */
    public function setTimestampOut(?\DateTime $timestampOut): LibraryItemEvent
    {
        $this->timestampOut = $timestampOut;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getReturnExpected(): ?\DateTime
    {
        return $this->returnExpected;
    }

    /**
     * @param \DateTime|null $returnExpected
     * @return LibraryItemEvent
     */
    public function setReturnExpected(?\DateTime $returnExpected): LibraryItemEvent
    {
        $this->returnExpected = $returnExpected;
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
     * @param string|null $returnAction
     * @return LibraryItemEvent
     */
    public function setReturnAction(?string $returnAction): LibraryItemEvent
    {
        $this->returnAction = $returnAction;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getReturnActionPerson(): ?Person
    {
        return $this->returnActionPerson;
    }

    /**
     * @param Person|null $returnActionPerson
     * @return LibraryItemEvent
     */
    public function setReturnActionPerson(?Person $returnActionPerson): LibraryItemEvent
    {
        $this->returnActionPerson = $returnActionPerson;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestampReturn(): ?\DateTime
    {
        return $this->timestampReturn;
    }

    /**
     * @param \DateTime|null $timestampReturn
     * @return LibraryItemEvent
     */
    public function setTimestampReturn(?\DateTime $timestampReturn): LibraryItemEvent
    {
        $this->timestampReturn = $timestampReturn;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getInPerson(): ?Person
    {
        return $this->inPerson;
    }

    /**
     * @param Person|null $inPerson
     * @return LibraryItemEvent
     */
    public function setInPerson(?Person $inPerson): LibraryItemEvent
    {
        $this->inPerson = $inPerson;
        return $this;
    }

    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return array
     */
    public static function getReturnActionList(): array
    {
        return self::$returnActionList;
    }
}