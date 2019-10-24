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
use App\Manager\MessageManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Entity\LibraryItemEvent;
use Kookaburra\UserAdmin\Util\UserHelper;
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
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var int
     */
    private $renewalMaximum;

    /**
     * LibraryManager constructor.
     * @param MessageManager $messageManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(MessageManager $messageManager)
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
        if ($item->getStatus() !== 'Available')
            return $this;
        if (! $item->getResponsibleForStatus() instanceof Person)
            return $this;

        $item->setStatus('On Loan');
        $item->setTimestampStatus(new \DateTimeImmutable());
        if (!$item->getReturnExpected() instanceof \DateTimeImmutable)
            $item->setReturnExpected(new \DateTimeImmutable('+'.$item->getLibrary()->getLendingPeriod($this->getBorrowPeriod()).' days'));
        $item->setStatusRecorder(UserHelper::getCurrentUser());
        $em = ProviderFactory::getEntityManager();
        $em->persist($item);
        $em->flush();
        $this->getMessageManager()->add('success', 'Your request was completed successfully.');
        new LibraryEventManager($item, 'loan');
        return $this;
    }

    /**
     * returnAction
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function returnAction(LibraryItem $item): LibraryManager
    {
        $em = ProviderFactory::getEntityManager();
        $em->persist($item);
        $em->flush();
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
        $event = $item->getEvents(true)->first();

        $item->setStatus('Available')
            ->setTimestampStatus(new \DateTimeImmutable('now'))
            ->setStatusRecorder(UserHelper::getCurrentUser())
            ->setResponsibleForStatus(null)
        ;

        if ($item->getReturnAction() !== null)
        {

        }

        $event->setInPerson($item->getStatusRecorder())
            ->setTimestampReturn($item->getTimestampStatus())
            ->setStatus($item->getStatus());

        $em = ProviderFactory::getEntityManager();
        $em->persist($event);
        $em->persist($item);
        $em->flush();
    }

    /**
     * renewItem
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function renewItem(LibraryItem $item)
    {
        if ($this->isItemAvailableForRenew($item)) {
            $newReturnDate = $item->getReturnExpected()->add(new \DateInterval('P'.$this->getBorrowPeriod().'D'));
            $item->setReturnExpected($newReturnDate);
            $lastEvent = $item->getLastEvent();
            $now = new \DateTimeImmutable();
            $lastEvent->setInPerson(UserHelper::getCurrentUser())
                ->setTimestampReturn($now);
            $event = new LibraryItemEvent($item);
            $event->setType('Renew Loan')
                ->setOutPerson($lastEvent->getInPerson())
                ->setTimestampOut($now)
            ;

            $item->setLastEvent(null);
            $em = ProviderFactory::getEntityManager();
            $em->persist($event);
            $em->persist($lastEvent);
            $em->persist($item);
            $em->flush();
            dump($item,$lastEvent,$event);
        }
    }

    /**
     * isItemAvailableForRenew
     * @param LibraryItem $item
     * @return bool
     * @throws \Exception
     */
    private function isItemAvailableForRenew(LibraryItem $item): bool
    {
        $event = $item->getLastEvent();
        if ($event === null || $event->getTimestampReturn() !== null)
        {
            $this->getMessageManager()->add('error', 'The item is not available for renewal.');
            return false;
        }
        if ($event->getReturnAction() !== null) {
            $this->getMessageManager()->add('warning', 'The return of the item is required for "{action}"', ['{action}' => TranslationsHelper::translate($event->getReturnAction())]);
            return false;
        }
        if ($item->getDaysOnLoan() >= $this->getBorrowPeriod() * ($this->getRenewalMaximum() + 1)) {
            $this->getMessageManager()->add('warning', 'This item has already exceeded renewal allowances for this library.');
            return false;
        }
        return true;
    }
}