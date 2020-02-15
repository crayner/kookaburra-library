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
 * Time: 14:15
 */

namespace Kookaburra\Library\Manager;

use App\Manager\Entity\PaginationAction;
use App\Manager\Entity\PaginationColumn;
use App\Manager\Entity\PaginationFilter;
use App\Manager\Entity\PaginationRow;
use App\Manager\PaginationInterface;
use App\Manager\AbstractPaginationManager;
use App\Manager\ScriptManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\Library\Entity\Library;

/**
 * Class CataloguePagination
 * @package Kookaburra\Library\Manager
 */
class CataloguePagination extends AbstractPaginationManager
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
        $column->setLabel('SchoolID')
            ->setContentKey(['identifier','typeName'])
            ->setHelp('Type')
            ->setSort()
            ->setSearch()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Name')
            ->setContentKey(['name','producer'])
            ->setSearch()
            ->setHelp('Producer')
            ->setSort()
            ->setClass('column relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Location')
            ->setContentKey(['facility', 'locationDetail'])
            ->setSearch()
            ->setClass('column hidden sm:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Ownership')
            ->setContentKey('owner')
            ->setSort(false)
            ->setSearch()
            ->setHelp('User/Owner')
            ->setClass('column hidden md:table-cell relative pr-4 cursor-pointer widthAuto');
        $row->addColumn($column);

        $column = new PaginationColumn();
        $column->setLabel('Status')
            ->setContentKey(['status', 'borrowable'])
            ->setSearch()
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
        $action->setTitle('Lending')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-book-reader fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__loan_item')
            ->setDisplayWhen('isAvailable')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Action')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-cog fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__loan_item')
            ->setDisplayWhen('isNotAvailable')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Return')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-undo fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__return_item')
            ->setDisplayWhen('onLoan')
            ->setRouteParams(['item' => 'id']);
        $row->addAction($action);

        $action = new PaginationAction();
        $action->setTitle('Delete')
            ->setAClass('thickbox p-3 sm:p-0')
            ->setColumnClass('p-2 sm:p-3')
            ->setSpanClass('fas fa-trash-alt fa-fw fa-1-5x text-gray-700')
            ->setRoute('library__delete_item')
            ->setOnClick('areYouSure')
            ->setDisplayWhen('isLostOrDecommissioned')
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

        $libraries = ProviderFactory::getRepository(Library::class)->findAll();
        if (count($libraries) > 1 ) {
            foreach($libraries as $library) {
                $filter = new PaginationFilter();
                $filter->setName('Library: ' . $library->getName())
                    ->setValue($library->getName())
                    ->setLabel(['Library: {value}', ['{value}' => $library->getName()], 'Library'])
                    ->setGroup('Library')
                    ->setContentKey('libraryName');
                $row->addFilter($filter);
            }
        }

        $this->setRow($row);
        return $this;
    }
}