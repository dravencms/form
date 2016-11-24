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

namespace Dravencms\AdminModule\Components\Form\ItemGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\ItemRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Html;

/**
 * Description of ItemGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ItemRepository */
    private $itemRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemGroup */
    private $itemGroup;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemGrid constructor.
     * @param ItemGroup $itemGroup
     * @param ItemRepository $itemRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(ItemGroup $itemGroup, ItemRepository $itemRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();
        $this->itemGroup = $itemGroup;
        $this->baseGridFactory = $baseGridFactory;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->itemRepository->getItemQueryBuilder($this->itemGroup));

        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnText('latteName', 'Latte name')
            ->setCustomRender(function($row){
                return 'formItem_'.$row->getId();
            });

        $grid->addColumnText('type', 'Type')
            ->setCustomRender(function ($row) {

                $multiOptions = [Item::TYPE_SELECT, Item::TYPE_MULTISELECT, Item::TYPE_RADIOLIST, Item::TYPE_CHECKBOXLIST];

                if (in_array($row->type, $multiOptions)) {
                    $el = Html::el('a', 'Options');
                    $el->href = $this->presenter->link('ItemOption:', ['itemId' => $row->getId()]);
                    $el->class = 'btn btn-default btn-xs';
                    return Item::$typeList[$row->type] . ' ' . $el;
                } else {
                    return Item::$typeList[$row->type];
                }
            })
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnText('defaultValue', 'Default value')
            ->setFilterText()
            ->setSuggestion();

        if ($this->presenter->isAllowed('form', 'edit')) {
            $grid->addActionHref('edit', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('Item:edit', ['itemGroupId' => $this->itemGroup->getId(), 'id' => $row->getId()]);
                })
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('form', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat form %s ?', $row->name];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i form ?');
        }
        $grid->setExport();

        return $grid;
    }

    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        $items = $this->itemRepository->getById($id);
        foreach ($items AS $item)
        {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGrid.latte');
        $template->render();
    }
}
