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

namespace Dravencms\AdminModule\Components\Form\ItemGroupGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of ItemGroupGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemGroupGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var ItemGroupRepository */
    private $itemGroupRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Form */
    private $form;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemGroupGrid constructor.
     * @param Form $form
     * @param ItemGroupRepository $itemGroupRepository
     * @param BaseGridFactory $baseGridFactory
     * @param CurrentLocale $currentLocale
     * @param EntityManager $entityManager
     */
    public function __construct(
        Form $form,
        ItemGroupRepository $itemGroupRepository,
        BaseGridFactory $baseGridFactory,
        CurrentLocale $currentLocale,
        EntityManager $entityManager
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->itemGroupRepository = $itemGroupRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocale;
        $this->form = $form;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->itemGroupRepository->getItemGroupQueryBuilder($this->form, $this->currentLocale));

        /*$grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();*/

        $grid->addColumnBoolean('isShowName', 'Show name');


        if ($this->presenter->isAllowed('form', 'edit')) {

            $grid->addActionHref('Item', 'Items', 'Item:')
                ->setCustomHref(function($row){
                    return $this->presenter->link('Item:', ['itemGroupId' => $row->getId()]);
                })
                ->setIcon('bars');

            $grid->addActionHref('edit', 'Upravit')
                ->setCustomHref(function($row){
                   return $this->presenter->link('ItemGroup:edit', ['formId' => $this->form->getId(), 'id' => $row->getId()]);
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
                    return ['Opravdu chcete smazat item group %s ?', $row->name];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i item groups ?');
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
        $itemGroups = $this->itemGroupRepository->getById($id);
        foreach ($itemGroups AS $itemGroup)
        {
            $this->entityManager->remove($itemGroup);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGroupGrid.latte');
        $template->render();
    }
}
