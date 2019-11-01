<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 28/10/2019
 * Time: 08:26
 */

namespace Kookaburra\Library\Helper;

use App\Entity\Person;
use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\Library\Manager\LibraryInterface;
use Kookaburra\Library\Manager\LibraryManager;
use Kookaburra\Library\Manager\LibraryTrait;
use Kookaburra\UserAdmin\Util\UserHelper;
use Symfony\Component\Mailer\Transport;

class ReturnItem implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @var LibraryManager
     */
    private $libraryManager;

    /**
     * ReturnItem constructor.
     * @param LibraryManager $libraryManager
     */
    public function __construct(LibraryManager $libraryManager)
    {
        $this->libraryManager = $libraryManager;
    }

    /**
     * returnItem
     * @param LibraryItem $item
     * @throws \Exception
     */
    public function returnItem(LibraryItem $item)
    {
        $item->setStatus('Available')
            ->setTimestampStatus(new \DateTimeImmutable('now'))
            ->setStatusRecorder(UserHelper::getCurrentUser())
            ->setResponsibleForStatus(null)
        ;

        $em = ProviderFactory::getEntityManager();

        if ($item->getReturnAction() !== null)
        {
            $action = $item->getReturnAction();
            switch ($item->getReturnAction()->getReturnAction()) {
                case 'Make Available':
                    new MakeAvailableItem($item);
                    break;
                case 'Decommission':
                    new DecommissionItem($item);
                    break;
                case 'Repair':
                    new RepairItem($item);
                    break;
                case 'Reserve':
                    new ReserveItem($item, $this->getLibraryManager(), true);
                    break;
            }
            $em->remove($action);
            $item->setReturnAction(null);
        }

        $em->persist($item);
        $em->flush();
    }

    /**
     * @return LibraryManager
     */
    public function getLibraryManager(): LibraryManager
    {
        return $this->libraryManager;
    }

    /**
     * returnAction
     * @param LibraryItem $item
     * @return LibraryManager
     */
    public function returnAction(LibraryItem $item): ReturnItem
    {
        if ($item->getStatus() !== 'On Loan' && $item->getReturnAction())
            return $this->returnActionNow($item);
        $action = $item->getReturnAction();
        if (!$action->getActionBy() instanceof Person || $action->getReturnAction() === null || $action->getReturnAction() === '') {
            $item->setReturnAction(null);
        }
        $em = ProviderFactory::getEntityManager();
        $em->persist($item);
        $em->flush();
        return $this;
    }

    /**
     * returnActionNow
     * @param LibraryItem $item
     * @return ReturnItem
     * @throws \Exception
     */
    private function returnActionNow(LibraryItem $item): ReturnItem
    {
        $action = $item->getReturnAction();
        if (!$action->getActionBy() instanceof Person && $action->getReturnAction() !== 'Make Available') {
            $this->getMessageManager()->add('error', 'return.error.1', [], 'messages');
            return $this;
        }
        $em = ProviderFactory::getEntityManager();
        $now = new \DateTimeImmutable();
        switch ($action->getReturnAction()) {
            case 'Make Available':
                new MakeAvailableItem($item);
                break;
            case 'Repair':
                if ($item->getStatus() !== 'On Loan')
                {
                    new RepairItem($item);
                    $this->getMessageManager()->add('success', 'return.success.0', [], 'messages');
                }
                break;
            case 'Decommission':
                if ($item->getStatus() !== 'On Loan')
                {
                    new DecommissionItem($item);
                    $this->getMessageManager()->add('success', 'return.success.0', [], 'messages');
                }
                break;
            case 'Reserve':
                if ($item->getStatus() !== 'Decommissioned' && $item->getStatus() !== 'Available')
                {
                    new ReserveItem($item, $this->getLibraryManager());
                    $this->getMessageManager()->add('success', 'return.success.0', [], 'messages');
                } else {
                    $this->getMessageManager()->add('warning', 'This item is not available to reserve.', [], 'Library');
                }
                break;
            default:
                $item->getLastEvent();
                dump($item);
        }
        return $this;
    }

}