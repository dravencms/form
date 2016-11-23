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

namespace Dravencms\AdminModule\Components\Form;

use Dravencms\Components\BaseGridFactory;
use App\Model\Form\Entities\Item;
use App\Model\Form\Repository\ItemOptionRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * Description of ItemOptionGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemOptionGrid extends Control
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
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->itemOptionRepository->getItemOptionQueryBuilder($this->item));

        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        if ($this->presenter->isAllowed('form', 'edit')) {
            $grid->addActionHref('edit', 'Upravit')
                ->setCustomHref(function($row){
                    return $this->presenter->link('ItemOption:edit', ['itemId' => $this->item->getId(), 'id' => $row->getId()]);
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
