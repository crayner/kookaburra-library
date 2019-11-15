<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 3/11/2019
 * Time: 09:13
 */

namespace Kookaburra\Library\Manager;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\ReactPaginationInterface;
use App\Manager\ReactPaginationManager;
use App\Manager\ScriptManager;
use App\Util\TranslationsHelper;

/**
 * Class BrowsePagination
 * @package Manager
 */
class BrowsePagination extends ReactPaginationManager
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
     * @return ReactPaginationInterface
     */
    public function execute(): ReactPaginationInterface
    {
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('')
            ->setContentKey('imageLocation')
            ->setContentType('image')
            ->setClass('column relative pr-4 cursor-pointer widthAuto text-centre')
            ->setOptions(['class' => 'max200 user'])
        ;
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name','producer'])
            ->setHelp('Producer')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('ID')
            ->setContentKey(['identifier', 'status'])
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

        $action = new PaginationAction();
        $action->setTitle('View Description')
            ->setAClass('')
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