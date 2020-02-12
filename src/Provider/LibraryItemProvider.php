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
 * Date: 7/10/2019
 * Time: 14:43
 */

namespace Kookaburra\Library\Provider;

use App\Form\Entity\SearchAny;
use App\Manager\Traits\EntityTrait;
use App\Provider\EntityProviderInterface;
use App\Util\GlobalHelper;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\CatalogueSearch;
use Kookaburra\Library\Entity\LibraryItem;

/**
 * Class LibraryItemProvider
 * @package Kookaburra\Library\Provider
 */
class LibraryItemProvider implements EntityProviderInterface
{
    use EntityTrait;

    private $entityName = LibraryItem::class;

    /**
     * getCatalogueList
     * @param CatalogueSearch $search
     * @param bool $asArray
     * @return mixed
     * @throws \Exception
     */
    public function getCatalogueList(bool $asArray = false)
    {
        $result = $this->getRepository()->findAll();
        if ($asArray) {
            $content = [];
            TranslationsHelper::setDomain('Library');
            $row = [
                TranslationsHelper::translate('Library'),
                TranslationsHelper::translate('School ID'),
                TranslationsHelper::translate('Name Producer'),
                TranslationsHelper::translate('Type'),
                TranslationsHelper::translate('Location'),
                TranslationsHelper::translate('Ownership User/Owner'),
                TranslationsHelper::translate('Status Borrowable'),
                TranslationsHelper::translate('Purchase Date Vendor'),
                TranslationsHelper::translate('Details'),
            ];
            $content[] = $row;
            foreach($result as $data){
                $item = $data['LibraryItem'];
                $row = [];
                $row[] = $item->getLibrary()->getName();
                $row[] = strval($item->getIdentifier());
                $row[] = $item->getName().';'.$item->getProducer();
                $row[] = $item->getItemType();
                $row[] = $item->getSpace()->getName().';'.$item->getLocationDetail();
                $row[] = $item->getOwnershipType() === 'School' ? GlobalHelper::getParam('organisation_name') : $this->getOwnership()->formatName();
                $row[] = $item->getStatus().';'.($item->isBorrowable() ? TranslationsHelper::translate('Yes', [], 'messages') : TranslationsHelper::translate('No', [], 'messages'));
                $row[] = trim(($item->getPurchaseDate() ? $item->getPurchaseDate()->format('Y-m-d') : '') . ' ' . ($item->getVendor() ?: TranslationsHelper::translate('Unknown')));
                $row[] = implode(' ; ', array_map(
                    function ($v, $k) {
                        if(is_array($v)){
                            return $k.'[]:'.implode('&'.$k.'[]=', $v);
                        } else {
                            return $k.':'.$v;
                        }
                    },
                    $item->getFields(),
                    array_keys($item->getFields())
                ));

                $content[] = $row;
            }
            return $content;
        }
        return $result;
    }

    /**
     * getBrowserList
     * @param SearchAny $search
     * @return mixed
     * @throws \Exception
     */
    public function getBrowserList(SearchAny $search)
    {
        return $this->getRepository()->findByFullSearch($search);
    }
}