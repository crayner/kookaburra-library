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
 * Date: 3/11/2019
 * Time: 09:13
 */

namespace Kookaburra\Library\Manager;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Manager\ScriptManager;
use App\Util\TranslationsHelper;

/**
 * Class BorrowerPagination
 * @package Kookaburra\Library\Manager
 */
class BorrowerPagination extends AbstractPaginationManager
{
    /**
     * CataloguePagination constructor.
     */
    public function __construct(ScriptManager $scriptManager)
    {
        parent::__construct($scriptManager);
        TranslationsHelper::setDomain('Library');
    }

    /**
     * execute
     * @return PaginationInterface
     */
    public function execute(): PaginationInterface
    {
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('')
            ->setContentKey('imageLocation')
            ->setContentType('image')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
            ->setOptions(['class' => 'max240 user'])
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name','producer'])
            ->setHelp('Author/Producer')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Identifier')
            ->setContentKey(['status', 'borrowable'])
            ->setSort(false)
            ->setHelp('Status')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Location')
            ->setContentKey(['space', 'locationDetail'])
            ->setSort(false)
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Borrow Date')
            ->setContentKey(['timestampStatus', 'returnDetails'])
            ->setSort(false)
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('View Description')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('column p-2 sm:p-3')
            ->setSpanClass('fas fa-info fa-fw fa-1-5x text-gray-700')
            ->setOnClick('displayInformation')
            ->setRoute('library__item_information')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}