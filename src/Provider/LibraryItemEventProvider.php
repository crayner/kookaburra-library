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
 * Date: 11/11/2019
 * Time: 14:44
 */

namespace Kookaburra\Library\Provider;

use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use Kookaburra\Library\Entity\BorrowerSearch;
use Kookaburra\Library\Entity\LibraryItemEvent;

/**
 * Class LibraryItemEventProvider
 * @package Kookaburra\Library\Provider
 */
class LibraryItemEventProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = LibraryItemEvent::class;

    /**
     * findBorrowerRecords
     * @param BorrowerSearch $search
     * @return array
     */
    public function findBorrowerRecords(BorrowerSearch $search): array
    {
        return $this->getRepository()->findBorrowerRecords($search);
    }
}