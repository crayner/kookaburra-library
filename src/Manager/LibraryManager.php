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

use Kookaburra\UserAdmin\Entity\Person;
use App\Entity\Setting;
use App\Manager\MessageManager;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\RapidLoan;
use Kookaburra\Library\Helper\LoanItem;
use Kookaburra\Library\Helper\RenewItem;
use Kookaburra\Library\Helper\ReturnAction;
use Kookaburra\Library\Helper\ReturnItem;
use Kookaburra\SystemAdmin\Notification\EventBuilderProvider;
use Symfony\Component\Routing\RouterInterface;

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
     * @var ReturnItem
     */
    private $returnItem;

    /**
     * @var RenewItem
     */
    private $renewItem;

    /**
     * @var LoanItem
     */
    private $loanItem;

    /**
     * @var ReturnAction
     */
    private $returnAction;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * LibraryManager constructor.
     * @param MessageManager $messageManager
     * @param EventBuilderProvider $provider Initiate event provider
     */
    public function __construct(MessageManager $messageManager, EventBuilderProvider $provider, RouterInterface $router)
    {
        $this->messageManager = $messageManager;
        $this->getMessageManager()->setDomain('Library');
        TranslationsHelper::setDomain('Library');
        $this->router = $router;
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
    public function newIdentifier(LibraryItem $item, bool $changeID = false): LibraryItem
    {
        if ((!in_array($item->getIdentifier(), [null, '']) || $item->getLibrary() === null) && !$changeID)
            return $item;
        $key = uniqid($item->getLibrary()->getAbbr() . '-');
        $ok = false;
        do {
            $ok = null === ProviderFactory::getRepository(LibraryItem::class)->findOneBy(['identifier' => $key]);
            if (!$ok)
                $key = uniqid($item->getLibrary()->getAbbr() . '-');
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

        if ($content['imageType'] === 'File' && is_string($content['imageLocation']) && strpos($content['imageLocation'], 'data:image') === 0) {
            $content['imageLocation'] = ImageHelper::convertJsonToImage($content['imageLocation'], 'library_', 'Library');
            $item->setImageLocation($content['imageLocation']);
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

        foreach (self::$itemTypes as $name => $type) {
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

        foreach ($this->allowedBorrowers as $allowedType) {
            $allowedType = 'get' . rtrim($allowedType, 's') . 'Borrowers';
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
        foreach (ProviderFactory::getRepository(Person::class)->findAllStudentsByRollGroup() as $student) {
            $name = $student['fullName'] . ' (' . ($student['studentID'] === '' ? $student['id'] : $student['studentID']) . ')';
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
        foreach (ProviderFactory::getRepository(Person::class)->findCurrentStaff() as $staff) {
            $name = $staff->formatName(['initial' => false, 'reverse' => true]);
            $result['Staff'][$name] = $staff->getId();
        }

        return $result;
    }

    /**
     * getBorrowPeriod
     * @param LibraryItem $item
     * @return int
     */
    public function getBorrowPeriod(LibraryItem $item): int
    {
        return intval($item->getLibrary()->getLendingPeriod()) > 0 ? $item->getLibrary()->getLendingPeriod() : $this->borrowPeriod;
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
        foreach ($this->getItemType($item->getItemType())['fields'] as $name => $field) {
            $fields[$name] = $content['field' . $q];
            $q++;
        }
        return $fields;
    }

    /**
     * loanItem
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function loanItem(LibraryItem $item): LibraryManager
    {
        $this->getLoanItem()->invoke($item);
        return $this;
    }

    /**
     * loanItem
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function reserveToLoanItem(LibraryItem $item): LibraryManager
    {
        $this->getLoanItem()->reserveToLoanItem($item);
        return $this;
    }

    /**
     * returnItem
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function returnItem(LibraryItem $item): LibraryManager
    {
        $this->getReturnItem()->invoke($item);
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
     * getReturnItem
     * @return ReturnItem
     */
    private function getReturnItem(): ReturnItem
    {
        if (null === $this->returnItem) {
            $this->returnItem = new ReturnItem($this);
        }
        return $this->returnItem;
    }

    /**
     * getLoanItem
     * @return LoanItem
     */
    private function getLoanItem(): LoanItem
    {
        if (null === $this->loanItem) {
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
        if (null === $this->renewItem) {
            $this->renewItem = new RenewItem($this);
        }
        return $this->renewItem;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * quickLoanSearch
     * @param array $content
     * @param RapidLoan $loan
     */
    public function quickLoanSearch(array $content, RapidLoan $loan)
    {
        $search = $content['search'];

        // Look for a person
        $person = ProviderFactory::getRepository(Person::class)->findOneUsingQuickSearch($search);

        if (null === $person) {
            $item = ProviderFactory::getRepository(LibraryItem::class)->findOneUsingQuickSearch($search);
            if (null === $item) {
                $this->getMessageManager()->add('warning', 'No results were found for your search: "{search}"', ['{search}' => $search], 'Library');
                return ;
            }
            ProviderFactory::getEntityManager()->refresh($item);

            //  Deal with an item.
            if ($item->getStatus() === 'Available') {
                $loan->addItem($item);
                if ($loan->getPerson() instanceof Person)
                {
                    $item->setResponsibleForStatus($loan->getPerson());
                    $this->loanItem($item);
                    $loan->clear();
                }
                return;
            } elseif ($item->getStatus() === 'On Loan') {
                $this->returnItem($item);
                $this->getMessageManager()->add('success', 'The item "{name}" was returned to the library. Scan this item again to the add to loan list', ['{name}' => $item->getName()], 'Library');
                $em  = ProviderFactory::getEntityManager();
                $em->persist($item);
                $em->flush();
                return ;
            } else {
                $this->getMessageManager()->add('warning', 'The item "{name}" is not available for loan.', ['{name}' => $item->getName()], 'Library');
                return ;
            }
        }

        //handle a person
        if ($loan->getItems()->count() > 0) {
            foreach($loan->getItems() as $item)
            {
                $item->setResponsibleForStatus($person);
                $this->getLoanItem()->invoke($item);
            }
            $loan->clear();
            return ;
        } else {
            $loan->setPerson($person);
            return ;
        }
    }

    /**
     * transformItems
     * @param array $content
     * @param RapidLoan $loan
     * @return array
     */
    public function transformItems(array $content, RapidLoan $loan)
    {
        $items = $content['items'] ?: [];

        foreach ($items as $item) {
            $loan->addItem(ProviderFactory::getRepository(LibraryItem::class)->find($item['id']));
        }

        $content['items'] = null;
        return $content;
    }

    /**
     * canBorrow
     * @return bool
     */
    public function canBorrow(Person $person): bool
    {
        if (($borrowLimit = LibraryHelper::getCurrentLibrary()->getBorrowLimit()) === 0)
            return true;

        $currentLoanCount = ProviderFactory::getRepository(LibraryItem::class)->countOnLoanToPerson($person);

        return intval($currentLoanCount) < intval($borrowLimit);
    }

    /**
     * @return ReturnAction
     */
    public function getReturnAction(): ReturnAction
    {
        if (null === $this->returnAction)
            $this->returnAction = new ReturnAction();
        return $this->returnAction;
    }

    /**
     * returnAction
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function returnAction(LibraryItem $item): LibraryManager
    {
        $this->getReturnAction()->invoke($item);
        return $this;
    }
}