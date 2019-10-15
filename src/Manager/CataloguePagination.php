<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/10/2019
 * Time: 14:15
 */

namespace Kookaburra\Library\Manager;


use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationRow;
use App\Manager\ReactPaginationInterface;
use App\Manager\ReactPaginationManager;

class CataloguePagination extends ReactPaginationManager
{
    public function execute(): ReactPaginationInterface
    {
        $row = new PaginationRow();

        $column = new PaginationColumn();
        $column->setLabel('SchoolID')
            ->setContentKey(['identifier','typeName'])
            ->setHelp('Type')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name','producer'])
            ->setHelp('Producer')
            ->setSort(true)
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Location')
            ->setContentKey(['space', 'locationDetail'])
            ->setSort(false)
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Ownership')
            ->setContentKey('owner')
            ->setSort(false)
            ->setHelp('User/Owner')
            ->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status', 'borrowable'])
            ->setSort(false)
            ->setHelp('Borrowable')
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $action = new PaginationAction();
        $action->setTitle('Edit')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('far fa-edit fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__edit')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__delete_item')
            ->setOnClick('areYouSure')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Duplicate')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-clone fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__duplicate_item')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $this->setRow($row);
        return $this;
    }
}