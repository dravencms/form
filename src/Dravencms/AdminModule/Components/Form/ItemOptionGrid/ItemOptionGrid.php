<?php

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\AdminModule\Components\Form\ItemOptionGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of ItemOptionGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemOptionGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ItemOptionRepository */
    private $itemOptionRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Item */
    private $item;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemOptionGrid constructor.
     * @param Item $item
     * @param ItemOptionRepository $itemOptionRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(Item $item, ItemOptionRepository $itemOptionRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();
        $this->item = $item;
        $this->baseGridFactory = $baseGridFactory;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->itemOptionRepository->getItemOptionQueryBuilder($this->item));

        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText();


        if ($this->presenter->isAllowed('form', 'edit'))
        {
            $grid->addAction('edit', '', 'ItemOption:edit', ['itemId' => 'item.id', 'id'])
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'name');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids)
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(form, edit)
     */
    public function handleDelete($id)
    {
        $itemOptions = $this->itemOptionRepository->getById($id);
        foreach ($itemOptions AS $itemOption)
        {
            $this->entityManager->remove($itemOption);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemOptionGrid.latte');
        $template->render();
    }
}
