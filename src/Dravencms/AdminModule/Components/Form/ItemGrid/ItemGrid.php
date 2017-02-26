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
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
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

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var LocaleRepository */
    private $localeRepository;

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
     * @param CurrentLocale $currentLocale
     * @param LocaleRepository $localeRepository
     */
    public function __construct(
        ItemGroup $itemGroup,
        ItemRepository $itemRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocale $currentLocale,
        LocaleRepository $localeRepository
    )
    {
        parent::__construct();
        $this->itemGroup = $itemGroup;
        $this->baseGridFactory = $baseGridFactory;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocale;
        $this->localeRepository = $localeRepository;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->itemRepository->getItemQueryBuilder($this->itemGroup, $this->currentLocale, $this->localeRepository->getDefault()));

        $grid->addColumnText('item.name', 'Name')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnText('latteName', 'Latte name')
            ->setCustomRender(function($row){
                return 'formItem_'.$row->getId();
            });

        $grid->addColumnText('item.type', 'Type')
            ->setCustomRender(function ($row) {

                $multiOptions = [Item::TYPE_SELECT, Item::TYPE_MULTISELECT, Item::TYPE_RADIOLIST, Item::TYPE_CHECKBOXLIST];

                if (in_array($row->getItem()->getType(), $multiOptions)) {
                    $el = Html::el('a', 'Options');
                    $el->href = $this->presenter->link('ItemOption:', ['itemId' => $row->getItem()->getId()]);
                    $el->class = 'btn btn-default btn-xs';
                    return Item::$typeList[$row->getItem()->getType()] . ' ' . $el;
                } else {
                    return Item::$typeList[$row->getItem()->getType()];
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
                    return $this->presenter->link('Item:edit', ['itemGroupId' => $this->itemGroup->getId(), 'id' => $row->getItem()->getId()]);
                })
                ->setIcon('pencil');
        }

        if ($this->presenter->isAllowed('form', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getItem()->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat form %s ?', $row->getItem()->getName()];
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
