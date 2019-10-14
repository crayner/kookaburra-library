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

use App\Entity\Department;
use App\Entity\Person;
use App\Entity\SchoolYear;
use App\Entity\Space;
use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\UserAdmin\Util\UserHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LibraryItem
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryItemRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryItem", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})})
 * @ORM\HasLifecycleCallbacks()
 */
class LibraryItem implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", name="gibbonLibraryItemID", columnDefinition="INT(10) UNSIGNED ZEROFILL AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Library|null
     * @ORM\ManyToOne(targetEntity="Library")
     * @ORM\JoinColumn(name="library", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $library;

    /**
     * @var LibraryType|null
     * @ORM\ManyToOne(targetEntity="LibraryType")
     * @ORM\JoinColumn(name="gibbonLibraryTypeID", referencedColumnName="gibbonLibraryTypeID", nullable=false)
     * @Assert\NotBlank()
     */
    private $libraryType;

    /**
     * @var string|null
     * @ORM\Column(name="id",unique=true)
     * @Assert\NotBlank()
     */
    private $identifier;

    /**
     * @var string|null
     * @ORM\Column(options={"comment": "Name for book, model for computer, etc."}))
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(options={"comment": "Author for book, manufacturer for computer, etc"}))
     * @Assert\NotBlank()
     */
    private $producer;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $fields;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $vendor;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date",name="purchaseDate",nullable=true)
     */
    private $purchaseDate;

    /**
     * @var string|null
     * @ORM\Column(length=50,name="invoiceNumber",nullable=true)
     */
    private $invoiceNumber;

    /**
     * @var string|null
     * @ORM\Column(name="imageType", length=4, options={"comment": "Type of image. Image should be 240px x 240px, or smaller."})
     * @Assert\Choice(callback="getImageTypeList")
     */
    private $imageType = '';

    /**
     * @var array
     */
    private static $imageTypeList = ['', 'Link', 'File'];

    /**
     * @var string|null
     * @ORM\Column(name="imageLocation",options={"comment": "URL or local FS path of image."},nullable=true)
     * @Assert\Url()
     */
    private $imageLocation;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @var Space|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumn(name="gibbonSpaceID", referencedColumnName="gibbonSpaceID", nullable=true)
     */
    private $space;

    /**
     * @var string|null
     * @ORM\Column(name="locationDetail")
     */
    private $locationDetail;

    /**
     * @var string|null
     * @ORM\Column(name="ownershipType", length=12, options={"default": "School"})
     * @Assert\Choice(callback="getOwnershipTypeList")
     */
    private $ownershipType = 'School';
    
    /**
     * @var array 
     */
    private static $ownershipTypeList = ['School', 'Individual'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDOwnership", referencedColumnName="gibbonPersonID", nullable=true)
     * If owned by school, then this is the main user. If owned by individual, then this is that individual.
     */
    private $ownership;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Department")
     * @ORM\JoinColumn(name="gibbonDepartmentID", referencedColumnName="gibbonDepartmentID", nullable=true)
     * Who is responsible for managing this item? By default this will be the person who added the record, but it can be changed.
     */
    private $department;

    /**
     * @var string|null
     * @ORM\Column(name="replacement", length=1, options={"default": "Y"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $replacement = 'Y';

    /**
     * @var string|null
     * @ORM\Column(name="replacementCost", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $replacementCost;

    /**
     * @var SchoolYear|null
     * @ORM\ManyToOne(targetEntity="App\Entity\SchoolYear")
     * @ORM\JoinColumn(name="gibbonSchoolYearIDReplacement", referencedColumnName="gibbonSchoolYearID", nullable=true)
     */
    private $replacementYear;

    /**
     * @var string|null
     * @ORM\Column(name="physicalCondition", length=16)
     * @Assert\Choice(callback="getPhysicalConditionList")
     */
    private $physicalCondition = '';

    /**
     * @var array
     */
    private static $physicalConditionList = ['','As New','Lightly Worn','Moderately Worn','Damaged','Unusable'];

    /**
     * @var string|null
     * @ORM\Column(name="bookable", length=1, options={"default": "N"})
     * @Assert\Choice(callback="getBooleanList")
     */
    private $bookable = 'N';

    /**
     * @var string|null
     * @ORM\Column(name="borrowable", length=1, options={"default": "Y"}))
     * @Assert\Choice(callback="getBooleanList")
     */
    private $borrowable = 'Y';

    /**
     * @var string|null
     * @ORM\Column(name="status", length=16, options={"comment": "The current status of the item.", "default": "Available"})
     * @Assert\Choice(callback="getStatusList")
     */
    private $status = 'Available';

    /**
     * @var array
     */
    private static $statusList = ['Available','In Use','Decommissioned','Lost','On Loan','Repair','Reserved'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDStatusResponsible", referencedColumnName="gibbonPersonID", nullable=true)
     * The person who is responsible for the current status.
     */
    private $responsibleForStatus;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDStatusRecorder", referencedColumnName="gibbonPersonID", nullable=true)
     * The person who recorded the current status.
     */
    private $statusRecorder;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="timestampStatus", type="datetime", options={"comment": "The time the status was recorded"}, nullable=true)
     */
    private $timestampStatus;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="returnExpected", type="date", options={"comment": "The time when the event expires."}, nullable=true)
     */
    private $returnExpected;

    /**
     * @var string|null
     * @ORM\Column(name="returnAction", length=16, options={"comment": "What to do when the item is returned?"}, nullable=true)
     * @Assert\Choice(callback="getReturnActionList")
     */
    private $returnAction;

    /**
     * @var array
     */
    private static $returnActionList = ['', 'Make Available','Decommission','Repair','Reserve'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDReturnAction", referencedColumnName="gibbonPersonID", nullable=true)
     */
    private $personReturnAction;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDCreator", referencedColumnName="gibbonPersonID")
     */
    private $personCreator;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", name="timestampCreator")
     */
    private $timestampCreator;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ORM\JoinColumn(name="gibbonPersonIDUpdate", referencedColumnName="gibbonPersonID", nullable=true)
     */
    private $personUpdate;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", name="timestampUpdate", nullable=true)
     */
    private $timestampUpdate;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return LibraryItem
     */
    public function setId(?int $id): LibraryItem
    {
        $this->id = $id;
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
     * @return LibraryItem
     */
    public function setLibrary(?Library $library): LibraryItem
    {
        $this->library = $library;
        return $this;
    }

    /**
     * @return LibraryType|null
     */
    public function getLibraryType(): ?LibraryType
    {
        return $this->libraryType;
    }

    /**
     * @param LibraryType|null $libraryType
     * @return LibraryItem
     */
    public function setLibraryType(?LibraryType $libraryType): LibraryItem
    {
        $this->libraryType = $libraryType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string|null $identifier
     * @return LibraryItem
     */
    public function setIdentifier(?string $identifier): LibraryItem
    {
        $this->identifier = $identifier;
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
     * @return LibraryItem
     */
    public function setName(?string $name): LibraryItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProducer(): ?string
    {
        return $this->producer;
    }

    /**
     * @param string|null $producer
     * @return LibraryItem
     */
    public function setProducer(?string $producer): LibraryItem
    {
        $this->producer = $producer;
        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields = $this->fields ?: [];
    }

    /**
     * @param array|null $fields
     * @return LibraryItem
     */
    public function setFields(?array $fields): LibraryItem
    {
        $this->fields = $fields ?: [];
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    /**
     * @param string|null $vendor
     * @return LibraryItem
     */
    public function setVendor(?string $vendor): LibraryItem
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPurchaseDate(): ?\DateTime
    {
        return $this->purchaseDate;
    }

    /**
     * @param \DateTime|null $purchaseDate
     * @return LibraryItem
     */
    public function setPurchaseDate(?\DateTime $purchaseDate): LibraryItem
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    /**
     * @param string|null $invoiceNumber
     * @return LibraryItem
     */
    public function setInvoiceNumber(?string $invoiceNumber): LibraryItem
    {
        $this->invoiceNumber = $invoiceNumber;
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
     * @param string|null $imageType
     * @return LibraryItem
     */
    public function setImageType(?string $imageType): LibraryItem
    {
        $this->imageType = in_array($imageType, self::getImageTypeList()) ? $imageType : '';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageLocation(): ?string
    {
        return $this->imageLocation;
    }

    /**
     * @param string|null $imageLocation
     * @return LibraryItem
     */
    public function setImageLocation(?string $imageLocation): LibraryItem
    {
        $this->imageLocation = $imageLocation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return LibraryItem
     */
    public function setComment(?string $comment): LibraryItem
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     * @return LibraryItem
     */
    public function setSpace(?Space $space): LibraryItem
    {
        $this->space = $space;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocationDetail(): ?string
    {
        return $this->locationDetail;
    }

    /**
     * @param string|null $locationDetail
     * @return LibraryItem
     */
    public function setLocationDetail(?string $locationDetail): LibraryItem
    {
        $this->locationDetail = $locationDetail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOwnershipType(): ?string
    {
        return $this->ownershipType;
    }

    /**
     * @param string|null $ownershipType
     * @return LibraryItem
     */
    public function setOwnershipType(?string $ownershipType): LibraryItem
    {
        $this->ownershipType = in_array($ownershipType, self::getOwnershipTypeList()) ? $ownershipType : 'School';
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getOwnership(): ?Person
    {
        return $this->ownership;
    }

    /**
     * @param Person|null $ownership
     * @return LibraryItem
     */
    public function setOwnership(?Person $ownership): LibraryItem
    {
        $this->ownership = $ownership;
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
     * @param Department|null $department
     * @return LibraryItem
     */
    public function setDepartment(?Department $department): LibraryItem
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    /**
     * @param string|null $replacement
     * @return LibraryItem
     */
    public function setReplacement(?string $replacement): LibraryItem
    {
        $this->replacement = self::checkBoolean($replacement);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReplacementCost(): ?string
    {
        return $this->replacementCost;
    }

    /**
     * @param string|null $replacementCost
     * @return LibraryItem
     */
    public function setReplacementCost(?string $replacementCost): LibraryItem
    {
        $this->replacementCost = $replacementCost;
        return $this;
    }

    /**
     * @return SchoolYear|null
     */
    public function getReplacementYear(): ?SchoolYear
    {
        return $this->replacementYear;
    }

    /**
     * @param SchoolYear|null $replacementYear
     * @return LibraryItem
     */
    public function setReplacementYear(?SchoolYear $replacementYear): LibraryItem
    {
        $this->replacementYear = $replacementYear;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhysicalCondition(): ?string
    {
        return $this->physicalCondition;
    }

    /**
     * @param string|null $physicalCondition
     * @return LibraryItem
     */
    public function setPhysicalCondition(?string $physicalCondition): LibraryItem
    {
        $this->physicalCondition = in_array($physicalCondition, self::getPhysicalConditionList()) ? $physicalCondition : '';
        return $this;
    }

    /**
     * @return string
     */
    public function getBookable(): string
    {
        return $this->bookable = self::checkBoolean($this->bookable, 'N');
    }

    /**
     * @param string|null $bookable
     * @return LibraryItem
     */
    public function setBookable(?string $bookable): LibraryItem
    {
        $this->bookable = self::checkBoolean($bookable, 'N');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBorrowable(): ?string
    {
        return $this->borrowable;
    }

    /**
     * @param string|null $borrowable
     * @return LibraryItem
     */
    public function setBorrowable(?string $borrowable): LibraryItem
    {
        $this->borrowable = self::checkBoolean($borrowable);
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
     * @return LibraryItem
     */
    public function setStatus(?string $status): LibraryItem
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
     * @return LibraryItem
     */
    public function setResponsibleForStatus(?Person $responsibleForStatus): LibraryItem
    {
        $this->responsibleForStatus = $responsibleForStatus;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getStatusRecorder(): ?Person
    {
        return $this->statusRecorder;
    }

    /**
     * @param Person|null $statusRecorder
     * @return LibraryItem
     */
    public function setStatusRecorder(?Person $statusRecorder): LibraryItem
    {
        $this->statusRecorder = $statusRecorder;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestampStatus(): ?\DateTime
    {
        return $this->timestampStatus;
    }

    /**
     * @param \DateTime|null $timestampStatus
     * @return LibraryItem
     */
    public function setTimestampStatus(?\DateTime $timestampStatus): LibraryItem
    {
        $this->timestampStatus = $timestampStatus;
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
     * @return LibraryItem
     */
    public function setReturnExpected(?\DateTime $returnExpected): LibraryItem
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
     * @return LibraryItem
     */
    public function setReturnAction(?string $returnAction): LibraryItem
    {
        $this->returnAction = in_array($returnAction, self::getReturnActionList()) ? $returnAction : '';
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPersonReturnAction(): ?Person
    {
        return $this->personReturnAction;
    }

    /**
     * @param Person|null $personReturnAction
     * @return LibraryItem
     */
    public function setPersonReturnAction(?Person $personReturnAction): LibraryItem
    {
        $this->personReturnAction = $personReturnAction;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPersonCreator(): ?Person
    {
        return $this->personCreator;
    }

    /**
     * @param Person|null $personCreator
     * @return LibraryItem
     */
    public function setPersonCreator(?Person $personCreator): LibraryItem
    {
        $this->personCreator = $personCreator;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestampCreator(): ?\DateTime
    {
        return $this->timestampCreator;
    }

    /**
     * @param \DateTime|null $timestampCreator
     * @return LibraryItem
     */
    public function setTimestampCreator(?\DateTime $timestampCreator): LibraryItem
    {
        $this->timestampCreator = $timestampCreator;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPersonUpdate(): ?Person
    {
        return $this->personUpdate;
    }

    /**
     * @param Person|null $personUpdate
     * @return LibraryItem
     */
    public function setPersonUpdate(?Person $personUpdate): LibraryItem
    {
        $this->personUpdate = $personUpdate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTimestampUpdate(): ?\DateTime
    {
        return $this->timestampUpdate;
    }

    /**
     * @param \DateTime|null $timestampUpdate
     * @return LibraryItem
     */
    public function setTimestampUpdate(?\DateTime $timestampUpdate): LibraryItem
    {
        $this->timestampUpdate = $timestampUpdate;
        return $this;
    }

    /**
     * @return array
     */
    public static function getImageTypeList(): array
    {
        return self::$imageTypeList;
    }

    /**
     * @return array
     */
    public static function getOwnershipTypeList(): array
    {
        return self::$ownershipTypeList;
    }

    /**
     * @return array
     */
    public static function getPhysicalConditionList(): array
    {
        return self::$physicalConditionList;
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

    /**
     * update
     * @return LibraryItem
     * @ORM\PreUpdate()
     */
    public function update(): LibraryItem
    {
        if (null === $this->getSpace() || '' === $this->getSpace())
            $this->setSpace($this->getLibrary()->getFacility());
        return $this->setTimestampUpdate(new \DateTime())->setPersonUpdate(UserHelper::getCurrentUser());
    }

    /**
     * persist
     * @return LibraryItem
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function persist(): LibraryItem
    {
        if (null === $this->getSpace() || '' === $this->getSpace())
            $this->setSpace($this->getLibrary()->getFacility());
        return $this->update()->setPersonCreator(UserHelper::getCurrentUser())->setTimestampCreator(new \DateTime());
    }

    /**
     * __toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->getIdentifier() . ': ' . $this->getName();
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'identifier' => $this->getIdentifier(),
            'producer' => $this->getProducer(),
            'typeName' => $this->getLibraryType()->getName(),
            'space' => $this->getSpace()->getName(),
            'locationDetail' => $this->getLocationDetail(),
            'owner' => $this->getOwnershipType() === 'Individual' ? $this->getOwnership()->formatName(false): ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'organisationName'),
            'status' => TranslationsHelper::translate($this->getStatus()),
            'borrowable' => $this->getBorrowable() ? TranslationsHelper::translate('Yes') : TranslationsHelper::translate('No'),
        ];
    }
}