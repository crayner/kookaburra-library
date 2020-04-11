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

use Kookaburra\UserAdmin\Entity\Person;
use Kookaburra\SystemAdmin\Entity\Setting;
use App\Manager\EntityInterface;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use Doctrine\ORM\Mapping as ORM;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class LibraryItemEvent
 * @package Kookaburra\Library\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\Library\Repository\LibraryItemEventRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="LibraryItemEvent",
 *     indexes={@ORM\Index(name="item", columns={"library_item"}),
 *     @ORM\Index(name="responsible_for_status", columns={"responsible_for_status"}),
 *     @ORM\Index(name="person_in", columns={"person_in"}),
 *     @ORM\Index(name="person_out", columns={"person_out"})})
 * @ORM\HasLifecycleCallbacks()
 */
class LibraryItemEvent implements EntityInterface
{
    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id", columnDefinition="INT(14) UNSIGNED")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var LibraryItem|null
     * @ORM\ManyToOne(targetEntity="LibraryItem", inversedBy="events")
     * @ORM\JoinColumn(name="library_item", referencedColumnName="id", nullable=false)
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
    private static $typeList = ['Decommission','Loss','Loan','Repair','Reserve','Other','Renew Loan'];

    /**
     * @var string|null
     * @ORM\Column(name="status", length=16, options={"default": "Available"})
     */
    private $status = 'Available';

    /**
     * @var array
     */
    private static $statusList = ['Available','Decommissioned','Lost','On Loan','Repair','Reserved','Returned','In Use'];

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="responsible_for_status", referencedColumnName="id", nullable=true)
     * The person who was responsible for the event.
     */
    private $responsibleForStatus;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="person_out", referencedColumnName="id", nullable=true)
     */
    private $outPerson;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(name="timestamp_out", type="datetime_immutable", nullable=true, options={"comment": "The time the event was recorded"})
     */
    private $timestampOut;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(name="timestamp_in", type="datetime_immutable", nullable=true)
     */
    private $timestampReturn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Kookaburra\UserAdmin\Entity\Person")
     * @ORM\JoinColumn(name="person_in", referencedColumnName="id", nullable=true)
     */
    private $inPerson;

    /**
     * LibraryItemEvent constructor.
     * @param LibraryItem|null $item
     */
    public function __construct(?LibraryItem $item)
    {
        if ($item instanceof LibraryItem) {
            $this->setLibraryItem($item)
                ->setStatus($item->getStatus())
                ->setResponsibleForStatus($item->getResponsibleForStatus())
            ;
        }
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
     * @return \DateTimeImmutable|null
     */
    public function getTimestampOut(): ?\DateTimeImmutable
    {
        return $this->timestampOut;
    }

    /**
     * TimestampOut.
     *
     * @param \DateTimeImmutable|null $timestampOut
     * @return LibraryItemEvent
     */
    public function setTimestampOut(?\DateTimeImmutable $timestampOut): LibraryItemEvent
    {
        $this->timestampOut = $timestampOut;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimestampReturn(): ?\DateTimeImmutable
    {
        return $this->timestampReturn;
    }

    /**
     * TimestampReturn.
     *
     * @param \DateTimeImmutable|null $timestampReturn
     * @return LibraryItemEvent
     */
    public function setTimestampReturn(?\DateTimeImmutable $timestampReturn): LibraryItemEvent
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
     * createTimestampOutPerson
     * @return LibraryItemEvent
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function createTimestampOutPerson()
    {
        return $this->setOutPerson(UserHelper::getCurrentUser())
            ->setTimestampOut(new \DateTimeImmutable());
    }


    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {

        $returnDetails = $this->getStatus();
        if ($this->getTimestampReturn() === null && $this->getLibraryItem()->getStatus() === 'On Loan') {
            if ($this->getLibraryItem()->getReturnExpected() !== null && $this->getLibraryItem()->getReturnExpected()->format('Ymd') < date('Ymd')) {
                $diff = $this->getLibraryItem()->getReturnExpected()->diff(new \DateTimeImmutable());
                $returnDetails = '<span class="text-red-400">' . TranslationsHelper::translate('Overdue ({days})', ['{days}' => $diff->days], 'Library') . '</span>';
            }
        } else {
            $returnDetails = $this->getTimestampReturn()->format('D jS M/Y');
        }

        return [
            'name' => $this->getLibraryItem()->getName(),
            'id' => $this->getLibraryItem()->getId(),
            'identifier' => $this->getLibraryItem()->getIdentifier(),
            'producer' => $this->getLibraryItem()->getProducer(),
            'typeName' => $this->getLibraryItem()->getItemType(),
            'space' => $this->getLibraryItem()->getSpace()->getName(),
            'locationDetail' => $this->getLibraryItem()->getLocationDetail(),
            'owner' => $this->getLibraryItem()->getOwnershipType() === 'Individual' ? $this->getLibraryItem()->getOwnership()->formatName(false): ProviderFactory::create(Setting::class)->getSettingByScopeAsString('System', 'organisationName'),
            'status' => TranslationsHelper::translate($this->getStatus()),
            'borrowable' => $this->getLibraryItem()->isBorrowable() ? TranslationsHelper::translate('Yes') : TranslationsHelper::translate('No'),
            'isAvailable' => $this->getLibraryItem()->isAvailable(),
            'isNotAvailable' => !$this->getLibraryItem()->isAvailable(),
            'isLostOrDecommissioned' => in_array($this->getLibraryItem()->getStatus(), ['Lost', 'Decommissioned']),
            'onLoan' => $this->getLibraryItem()->getStatus() === 'On Loan' && $this->getLibraryItem()->isBorrowable(),
            'imageLocation' => ImageHelper::getAbsoluteImageURL($this->getLibraryItem()->getImageType(), $this->getLibraryItem()->getImageLocation()),
            'fullString' => $this->getLibraryItem()->toFullString(),
            'timestampStatus' => $this->getTimestampOut() ? $this->getTimestampOut()->format('D jS M/Y') : '',
            'returnDetails' => $returnDetails,
        ];
    }
}