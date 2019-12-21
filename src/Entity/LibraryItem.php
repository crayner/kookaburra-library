<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\Library\Entity;

use App\Entity\Department;
use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\SchoolAdmin\Entity\SchoolYear;
use App\Entity\Setting;
use App\Entity\Space;
use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\UserAdmin\Util\UserHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LibraryItem
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryItemRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryItem", uniqueConstraints={@ORM\UniqueConstraint(name="identifier", columns={"identifier"})},
 *     indexes={
 *          @ORM\Index(name="item_type", columns={"item_type"}),
 *     @ORM\Index(name="library", columns={"library_id"}),
 *     @ORM\Index(name="person_ownership", columns={"person_ownership"}),
 *     @ORM\Index(name="department", columns={"department_id"}),
 *     @ORM\Index(name="created_by", columns={"created_by"}),
 *     @ORM\Index(name="replacement_year", columns={"replacement_year"}),
 *     @ORM\Index(name="responsible_for_status", columns={"responsible_for_status"}),
 *     @ORM\Index(name="space", columns={"facility_id"}),
 *     @ORM\Index(name="status_recorder", columns={"status_recorder"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class LibraryItem implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(10) UNSIGNED ZEROFILL")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Library|null
     * @ORM\ManyToOne(targetEntity="Library")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $library;

    /**
     * @var string|null
     * @ORM\Column(length=32)
     * @Assert\NotBlank()
     */
    private $itemType = 'Print Publication';

    /**
     * @var string|null
     * @ORM\Column(unique=true)
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
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $vendor;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $fields = [];

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",name="purchase_date",nullable=true)
     */
    private $purchaseDate;

    /**
     * @var string|null
     * @ORM\Column(length=50,name="invoice_number",nullable=true)
     */
    private $invoiceNumber;

    /**
     * @var string|null
     * @ORM\Column(name="image_type", length=4, options={"comment": "Type of image. Image should be 240px x 240px, or smaller."})
     * @Assert\Choice(callback="getImageTypeList")
     */
    private $imageType = '';

    /**
     * @var array
     */
    private static $imageTypeList = ['', 'Link', 'File'];

    /**
     * @var string|null
     * @ORM\Column(name="image_location",options={"comment": "URL or local FS path of image."},nullable=true)
     */
    private $imageLocation;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Space|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumn(name="facility_id", referencedColumnName="gibbonSpaceID", nullable=true)
     */
    private $space;

    /**
     * @var string|null
     * @ORM\Column(name="location_detail", nullable=true)
     */
    private $locationDetail;

    /**
     * @var string|null
     * @ORM\Column(name="ownership_type", length=12, options={"default": "School"})
     * @Assert\Choice(callback="getOwnershipTypeList")
     */
    private $ownershipType = 'School';
    
    /**
     * @var array 
     */
    private static $ownershipTypeList = ['School', 'Individual'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="person_ownership", referencedColumnName="gibbonPersonID", nullable=true)
     * If owned by school, then this is the main user. If owned by individual, then this is that individual.
     */
    private $ownership;

    /**
     * @var Department|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="gibbonDepartmentID", nullable=true)
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
     * @ORM\Column(name="replacement_cost", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $replacementCost;

    /**
     * @var SchoolYear|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\SchoolAdmin\Entity\SchoolYear")
     * @ORM\JoinColumn(name="replacement_year", referencedColumnName="gibbonSchoolYearID", nullable=true)
     */
    private $replacementYear;

    /**
     * @var string|null
     * @ORM\Column(name="physical_condition", length=16)
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
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="responsible_for_status", referencedColumnName="gibbonPersonID", nullable=true)
     * The person who is responsible for the current status. (borrower/repairer/etc)
     */
    private $responsibleForStatus;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="status_recorder", referencedColumnName="gibbonPersonID")
     * The person who recorded the current status.
     */
    private $statusRecorder;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(name="timestamp_status", type="datetime_immutable", options={"comment": "The time the status was recorded"}, nullable=true)
     */
    private $timestampStatus;

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(name="return_expected", type="date_immutable", options={"comment": "The time when the event expires."}, nullable=true)
     */
    private $returnExpected;

    /**
     * @var LibraryReturnAction
     * @ORM\OneToOne(targetEntity="Kookaburra\Library\Entity\LibraryReturnAction", mappedBy="item", orphanRemoval=true, cascade={"persist"})
     */
    private $returnAction;

    /**
     * @var DateTimeImmutable|null
     */
    private $audit;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="Kookaburra\Library\Entity\LibraryItemEvent", mappedBy="libraryItem")
     * @ORM\OrderBy({"timestampOut" = "DESC"})
     */
    private $events;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="gibbonPersonID")
     */
    private $createdBy;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", name="created_on")
     */
    private $createdOn;

    /**
     * @var LibraryItemEvent|null
     */
    private $lastEvent;

    /**
     * LibraryItem constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

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
     * getLibraryTypeList
     * @return array
     */
    public static function getItemTypeList(): array
    {
        return LibraryManager::getItemTypeList();
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
     * @return DateTimeImmutable|null
     */
    public function getPurchaseDate(): ?DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    /**
     * @param DateTimeImmutable|null $purchaseDate
     * @return LibraryItem
     */
    public function setPurchaseDate(?DateTimeImmutable $purchaseDate): LibraryItem
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
     * isBorrowable
     * @return bool
     */
    public function isBorrowable(): bool
    {
        return $this->getBorrowable() === 'Y';
    }

    /**
     * @return string
     */
    public function getBorrowable(): string
    {
        return $this->borrowable = self::checkBoolean($this->borrowable);
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
        if ($this->status === 'Available' && !$this->isBorrowable())
            return 'Reserved';
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return LibraryItem
     */
    public function setStatus(?string $status): LibraryItem
    {
        if ($status === 'Available' && !$this->isBorrowable())
            $status = 'Reserved';

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
     * @return DateTimeImmutable|null
     */
    public function getTimestampStatus(): ?DateTimeImmutable
    {
        return $this->timestampStatus;
    }

    /**
     * @param DateTimeImmutable|null $timestampStatus
     * @return LibraryItem
     */
    public function setTimestampStatus(?DateTimeImmutable $timestampStatus): LibraryItem
    {
        $this->timestampStatus = $timestampStatus;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getReturnExpected(): ?DateTimeImmutable
    {
        return $this->returnExpected;
    }

    /**
     * @param DateTimeImmutable|null $returnExpected
     * @return LibraryItem
     */
    public function setReturnExpected(?DateTimeImmutable $returnExpected): LibraryItem
    {
        $this->returnExpected = $returnExpected;
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
     * @return null|LibraryReturnAction
     */
    public function getReturnAction(): ?LibraryReturnAction
    {
        return $this->returnAction;
    }

    /**
     * @return array
     */
    public static function getReturnActionList(): array
    {
        return self::$returnActionList;
    }

    /**
     * ReturnItem.
     *
     * @param null|LibraryReturnAction $returnAction
     * @return LibraryItem
     */
    public function setReturnAction(?LibraryReturnAction $returnAction, bool $swap = true): LibraryItem
    {
        if($swap && $returnAction)
            $returnAction->setItem($this, false);
        $this->returnAction = $returnAction;
        return $this;
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
        if (null === $this->getDepartment() || '' === $this->getDepartment())
            $this->setDepartment($this->getLibrary()->getDepartment());
        if (!in_array($this->getStatus(), ['On Loan']))
            $this->setReturnExpected(null);
        if (null !== $this->getReturnAction() && !$this->getReturnAction()->isValidAction() && intval($this->getReturnAction()->getId()) === 0)
            $this->setReturnAction(null);
        return $this;
    }

    /**
     * persist
     * @return LibraryItem
     * @throws Exception
     * @ORM\PrePersist()
     */
    public function persist(): LibraryItem
    {
        if (null === $this->getSpace() || '' === $this->getSpace())
            $this->setSpace($this->getLibrary()->getFacility());
        if (null === $this->getDepartment() || '' === $this->getDepartment())
            $this->setDepartment($this->getLibrary()->getDepartment());
       return $this->update()->setCreatedBy(UserHelper::getCurrentUser())->setCreatedOn(new DateTimeImmutable());
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
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        return [
            'name' => $this->getName(),
            'id' => $this->getId(),
            'identifier' => $this->getIdentifier(),
            'producer' => $this->getProducer(),
            'typeName' => $this->getItemType(),
            'space' => $this->getSpace()->getName(),
            'locationDetail' => $this->getLocationDetail(),
            'owner' => $this->getOwnershipType() === 'Individual' ? $this->getOwnership()->formatName(false): ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'organisationName'),
            'status' => TranslationsHelper::translate($this->getStatus()),
            'borrowable' => $this->isBorrowable() ? TranslationsHelper::translate('Yes') : TranslationsHelper::translate('No'),
            'isAvailable' => $this->isAvailable(),
            'isNotAvailable' => !$this->isAvailable(),
            'isLostOrDecommissioned' => in_array($this->getStatus(), ['Lost', 'Decommissioned']),
            'onLoan' => $this->getStatus() === 'On Loan' && $this->isBorrowable(),
            'imageLocation' => ImageHelper::getAbsoluteImageURL($this->getImageType(), $this->getImageLocation()),
            'fullString' => $this->toFullString()
        ];
    }

    /**
     * getFields
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields ?: [];
    }

    /**
     * Fields.
     *
     * @param array $fields
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
    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    /**
     * ItemType.
     *
     * @param string|null $itemType
     * @return LibraryItem
     */
    public function setItemType(?string $itemType): LibraryItem
    {
        $this->itemType = $itemType;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getAudit(): ?DateTimeImmutable
    {
        return $this->audit;
    }

    /**
     * Audit.
     *
     * @param DateTimeImmutable|null $audit
     * @return LibraryItem
     */
    public function setAudit(?DateTimeImmutable $audit): LibraryItem
    {
        $this->audit = $audit;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getEvents(bool $sort = false): Collection
    {
        if (null === $this->events)
            $this->events = new ArrayCollection();

        if ($this->events instanceof PersistentCollection)
            $this->events->initialize();

        if ($sort && $this->events instanceof PersistentCollection) {
            $iterator = $this->events->getIterator();
            $iterator->uasort(
                function ($a, $b) {
                    return $a->getTimestampOut()->getTimestamp() > $b->getTimestampOut()->getTimestamp() ? -1 : 1;
                }
            );

            $this->events = new ArrayCollection(iterator_to_array($iterator, false));
        }

        return $this->events;
    }

    /**
     * Events.
     *
     * @param Collection|null $events
     * @return LibraryItem
     */
    public function setEvents(?Collection $events): LibraryItem
    {
        $this->events = $events ?: new ArrayCollection();
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
     * @return LibraryItem
     */
    public function setCreatedBy(?Person $createdBy): LibraryItem
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedOn(): DateTimeImmutable
    {
        return $this->createdOn;
    }

    /**
     * CreatedOn.
     *
     * @param DateTimeImmutable $createdOn
     * @return LibraryItem
     */
    public function setCreatedOn(DateTimeImmutable $createdOn): LibraryItem
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    /**
     * isAvailable
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available = $this->getStatus() === 'Available' && $this->isBorrowable();
    }

    /**
     * getLastEvent
     * @return LibraryItemEvent|null
     */
    public function getLastEvent(): ?LibraryItemEvent
    {
        if (null === $this->lastEvent && $this->getEvents(true)->count() >= 1)
        {
            $this->setLastEvent($this->getEvents()->first());
        }
        return $this->lastEvent;
    }

    /**
     * LastEvent.
     *
     * @param LibraryItemEvent|null $lastEvent
     * @return LibraryItem
     */
    public function setLastEvent(?LibraryItemEvent $lastEvent): LibraryItem
    {
        $this->lastEvent = $lastEvent;
        return $this;
    }

    /**
     * getDaysOnLoan
     * calculates the days since the Loan was made.
     * if not returned, then the days is calculated to the returnExpected date,
     * unless the date is now after the returnExpectedDate.
     * @return int
     */
    public function getDaysOnLoan(): int
    {
        if ($this->getStatus() !== 'On Loan')
            return 0;

        try {
            $start = new DateTimeImmutable($this->getTimestampStatus()->format('Y-m-d 00:00:00'));
            $last = $this->getReturnExpected();
            $now = new DateTimeImmutable(date('Y-m-d 00:00:00'));
        } catch (Exception $e) {
            return 0;
        }
        if ($now > $this->getReturnExpected())
            $last = $now;
        $diff = $last->diff($start);

        return $diff->days;
    }

    /**
     * @var
     */
    private $fullString;

    /**
     * toFullString
     * @return string
     */
    public function toFullString(): string
    {
        if (null === $this->fullString)
        {
            $result = $this->getName();
            $result .= $this->getIdentifier();
            $result .= $this->getItemType();
            $result .= $this->getComment();
            $result .= implode('', array_values($this->getFields()));
            $result .= $this->getProducer();
            $result .= $this->getSpace()->getName();
            $result .= $this->getLocationDetail();
            $result .= $this->getStatus();

            $this->fullString = $result;
        }
        return $this->fullString;
    }

    /**
     * countOverdueDays
     * @return int
     * @throws Exception
     */
    public function countOverdueDays(): int
    {
        if ($this->getStatus() === 'On Loan') {
            if ($this->getReturnExpected() < new DateTimeImmutable())
            {
                $diff = $this->getReturnExpected()->diff(new DateTimeImmutable());
                return intval($diff->days);
            }
            return 0;
        }
        return 0;
    }
}