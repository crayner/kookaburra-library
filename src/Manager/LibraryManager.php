<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:05
 */

namespace Kookaburra\Library\Manager;

use App\Entity\Person;
use App\Entity\Setting;
use App\Manager\MessageManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Helper\LoanItem;
use Kookaburra\Library\Helper\RenewItem;
use Kookaburra\Library\Helper\ReturnAction;
use Kookaburra\SystemAdmin\Notification\EventBuilderProvider;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class LibraryManager
 * @package Kookaburra\Library\Manager
 */
class LibraryManager
{
    /**
     * @var bool
     */
    private $generateIdentifier = true;

    /**
     * @var int
     */
    private $maximumCopies = 20;

    /**
     * @var array
     */
    private $allowedBorrowers;

    /**
     * @var array
     */
    private static $itemTypes;

    /**
     * @var int
     */
    private $borrowPeriod = 7;

    /**
     * @var int
     */
    private $reservePeriod = 7;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var int
     */
    private $renewalMaximum;

    /**
     * @var Person
     */
    private $libraryAdministrator;

    /**
     * @var ReturnAction
     */
    private $returnAction;

    /**
     * @var RenewItem
     */
    private $renewItem;

    /**
     * @var LoanItem
     */
    private $loanItem;

    /**
     * LibraryManager constructor.
     * @param MessageManager $messageManager
     * @param EventBuilderProvider $provider  Initiate event provider
     */
    public function __construct(MessageManager $messageManager, EventBuilderProvider $provider)
    {
        $this->messageManager = $messageManager;
        $this->getMessageManager()->setDomain('Library');
        TranslationsHelper::setDomain('Library');
    }

    /**
     * @return MessageManager
     */
    public function getMessageManager(): MessageManager
    {
        return $this->messageManager;
    }

    /**
     * @return bool
     */
    public function isGenerateIdentifier(): bool
    {
        return $this->generateIdentifier;
    }

    /**
     * GenerateIdentifier.
     *
     * @param bool $generateIdentifier
     * @return LibraryManager
     */
    public function setGenerateIdentifier(bool $generateIdentifier): LibraryManager
    {
        $this->generateIdentifier = $generateIdentifier;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaximumCopies(): int
    {
        return $this->maximumCopies;
    }

    /**
     * MaximumCopies.
     *
     * @param int $maximumCopies
     * @return LibraryManager
     */
    public function setMaximumCopies(int $maximumCopies): LibraryManager
    {
        $this->maximumCopies = $maximumCopies;
        return $this;
    }

    /**
     * newIdentifier
     * @param LibraryItem $item
     * @return LibraryItem
     */
    public function newIdentifier(LibraryItem $item): LibraryItem
    {
        $key = uniqid($item->getLibrary()->getAbbr().'-');
        $ok = false;
        do {
            $ok = null === ProviderFactory::getRepository(LibraryItem::class)->findOneBy(['identifier' => $key]);
            if (!$ok)
                $key = uniqid($item->getLibrary()->getAbbr().'-');
        } while (!$ok);

        return $item->setIdentifier($key);
    }

    /**
     * setTranslations
     */
    public function setTranslations()
    {
        TranslationsHelper::addTranslation('Please enter an ISBN13 or ISBN10 value before trying to get data from Google Books.');
        TranslationsHelper::addTranslation('The specified record cannot be found.');
    }

    /**
     * handleItem
     * @param LibraryItem $item
     * @param array $content
     * @return LibraryItem
     */
    public function handleItem(LibraryItem $item, array $content): LibraryItem
    {
        $library = ProviderFactory::getRepository(Library::class)->find($content['library']);
        $item->setLibrary($library);

        $item->setFields($this->buildFields($item, $content));

        if ($content['imageType'] === 'File' && strpos($content['imageLocation'], 'data:image') === 0)
        {
            $image = explode(',', $content['imageLocation']);
            $fileContent = base64_decode($image[1]);
            $fileName = uniqid('library_', true);
            $type = explode(';', $image[0]);
            $type = str_replace('data:image/', '', $type[0]);
            $path = realpath(__DIR__ . '/../../../../../public/uploads');
            if (!is_dir($path . '/library'))
                mkdir($path . '/library', '0755', true);
            $path = realpath(__DIR__ . '/../../../../../public/uploads/library');
            switch($type) {
                case 'jpeg':
                    $filePath = $path . '/' . $fileName . '.jpeg';
                    file_put_contents($filePath, $fileContent);
                    break;
                default:
                    dump($type . ' is not handled.');
            }
            $content['imageLocation'] = $filePath;
            $item->setImageLocation($filePath);
        }

        $em = ProviderFactory::getEntityManager();
        $em->refresh($library);
        $em->persist($item);
        $em->flush();
        return $item;
    }

    /**
     * getItemTypes
     * @return array
     */
    public function getItemTypes(): array
    {
        return self::$itemTypes ?: [];
    }

    /**
     * ItemTypes.
     *
     * @param array $itemTypes
     * @return LibraryManager
     */
    public function setItemTypes(?array $itemTypes): LibraryManager
    {
        self::$itemTypes = $itemTypes ?: [];
        return $this;
    }

    /**
     * ItemTypes.
     *
     * @param array $itemTypes
     * @return LibraryManager
     */
    public function mergeItemTypes(array $itemTypes): LibraryManager
    {
        self::$itemTypes = array_merge($this->getItemTypes(), $itemTypes);
        return $this;
    }

    /**
     * getItemType
     * @param string $name
     * @return array
     */
    public function getItemType(string $name): array
    {
        return $this->getItemTypes()[$name];
    }

    /**
     * getItemTypeList
     * @return array
     */
    public static function getItemTypeList(): array
    {
        $result = [];

        foreach(self::$itemTypes as $name=>$type)
        {
            if ($type['active'])
                $result[] = $name;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedBorrowers(): array
    {
        return $this->allowedBorrowers;
    }

    /**
     * AllowedBorrowers.
     *
     * @param array $allowedBorrowers
     * @return LibraryManager
     */
    public function setAllowedBorrowers(array $allowedBorrowers): LibraryManager
    {
        $this->allowedBorrowers = $allowedBorrowers;
        return $this;
    }

    /**
     * getBorrowerList
     * @return array
     */
    public function getBorrowerList(): array
    {
        $borrowers = [];

        foreach($this->allowedBorrowers as $allowedType)
        {
            $allowedType = 'get' . rtrim($allowedType, 's').'Borrowers';
            $borrowers = array_merge($borrowers, $this->$allowedType());
        }

        return $borrowers;
    }

    /**
     * getStudentBorrowers
     * @return array
     */
    private function getStudentBorrowers(): array
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Person::class)->findAllStudentsByRollGroup() as $student) {
            $name = $student['fullName'] . ' ('.  ($student['studentID'] === '' ? $student['id'] : $student['studentID']) . ')';
            $result[$student['rollGroup']][$name] = $student['id'];
        }

        return $result;
    }

    /**
     * getStaffBorrowers
     * @return array
     */
    private function getStaffBorrowers(): array
    {
        $result = [];
        foreach(ProviderFactory::getRepository(Person::class)->findCurrentStaff() as $staff) {
            $name = $staff->formatName(['initial' => false, 'reverse' => true]);
            $result['Staff'][$name] = $staff->getId();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getBorrowPeriod(): int
    {
        return $this->borrowPeriod;
    }

    /**
     * BorrowPeriod.
     *
     * @param int $borrowPeriod
     * @return LibraryManager
     */
    public function setBorrowPeriod(int $borrowPeriod): LibraryManager
    {
        $this->borrowPeriod = $borrowPeriod;
        return $this;
    }

    /**
     * buildFields
     * @param LibraryItem $item
     * @param array $content
     * @return array
     */
    private function buildFields(LibraryItem $item, array $content)
    {
        $q = 0;
        $fields = [];
        foreach($this->getItemType($item->getItemType())['fields'] as $name=>$field)
        {
            $fields[$name] = $content['field'.$q];
            $q++;
        }
        return $fields;
    }

    /**
     * loanItem
     * @param LibraryItem $item
     * @return LibraryManager
     * @throws \Exception
     */
    public function loanItem(LibraryItem $item): LibraryManager
    {
        $this->getLoanItem()->loanItem($item);
        return $this;
    }

    /**
     * loanItem
     * @param LibraryItem $item
     * @return LibraryManager
     * @throws \Exception
     */
    public function reserveToLoanItem(LibraryItem $item): LibraryManager
    {
        $this->getLoanItem()->reserveToLoanItem($item);
        return $this;
    }

    /**
     * returnAction
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function returnAction(LibraryItem $item): LibraryManager
    {
        $this->getReturnAction()->returnAction($item);
        return $this;
    }

    /**
     * getRenewalMaximum
     * @return int
     */
    public function getRenewalMaximum(): int
    {
        return $this->renewalMaximum = $this->renewalMaximum ?: 1;
    }

    /**
     * setRenewalMaximum
     * @param int $renewalMaximum
     * @return LibraryManager
     */
    public function setRenewalMaximum(int $renewalMaximum): LibraryManager
    {
        $this->renewalMaximum = $renewalMaximum ?: 1;
        return $this;
    }

    /**
     * returnItem
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function returnItem(LibraryItem $item)
    {
        $this->getReturnAction()->returnItem($item);
    }

    /**
     * renewItem
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function renewItem(LibraryItem $item)
    {
        $this->getRenewItem()->renewItem($item);
    }

    /**
     * @return Person
     */
    public function getLibraryAdministrator(): Person
    {
        return $this->libraryAdministrator;
    }

    /**
     * LibraryAdministrator.
     *
     * @param Person|null $libraryAdministrator
     * @return LibraryManager
     */
    public function setLibraryAdministrator(?string $username, ?string $email): LibraryManager
    {
        $libraryAdministrator = null;
        if ($username !== null)
            $libraryAdministrator = ProviderFactory::getRepository(Person::class)->findOneByUsername($username);
        if ($libraryAdministrator === null && $email !== null)
            $libraryAdministrator = ProviderFactory::getRepository(Person::class)->findOneByEmail($email);
        if ($libraryAdministrator === null)
            $libraryAdministrator = ProviderFactory::create(Setting::class)->getSettingByScopeAsObject('System', 'organisationAdministrator', Person::class);
        $this->libraryAdministrator = $libraryAdministrator;
        return $this;
    }

    /**
     * @return int
     */
    public function getReservePeriod(): int
    {
        return $this->reservePeriod;
    }

    /**
     * ReservePeriod.
     *
     * @param int $reservePeriod
     * @return LibraryManager
     */
    public function setReservePeriod(int $reservePeriod): LibraryManager
    {
        $this->reservePeriod = $reservePeriod;
        return $this;
    }

    /**
     * getReturnAction
     * @return ReturnAction
     */
    private function getReturnAction(): ReturnAction
    {
        if (null === $this->returnAction)
        {
            $this->returnAction = new ReturnAction($this);
        }
        return $this->returnAction;
    }

    /**
     * getLoanItem
     * @return LoanItem
     */
    private function getLoanItem(): LoanItem
    {
        if (null === $this->loanItem)
        {
            $this->loanItem = new LoanItem($this);
        }
        return $this->loanItem;
    }

    /**
     * getLoanItem
     * @return LoanItem
     */
    private function getRenewItem(): RenewItem
    {
        if (null === $this->renewItem)
        {
            $this->renewItem = new RenewItem($this);
        }
        return $this->renewItem;
    }
}