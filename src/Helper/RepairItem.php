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
 * Date: 30/10/2019
 * Time: 09:24
 */

namespace Kookaburra\Library\Helper;

use App\Provider\ProviderFactory;
use Kookaburra\Library\Entity\LibraryItem;
use Kookaburra\UserAdmin\Util\UserHelper;

/**
 * Class RepairItem
 * @package Kookaburra\Library\Helper
 */
class RepairItem
{
    /**
     * RepairItem constructor.
     */
    public function __construct(LibraryItem $item)
    {
        $em = ProviderFactory::getEntityManager();
        $action = $item->getReturnAction();
        $now = new \DateTimeImmutable();
        $item->setStatus('Repair')
            ->setTimestampStatus($now)
            ->setStatusRecorder(UserHelper::getCurrentUser())
            ->setReturnAction(null)
            ->setResponsibleForStatus($action->getActionBy());
        $em->persist($item);
        $em->flush();
    }
}