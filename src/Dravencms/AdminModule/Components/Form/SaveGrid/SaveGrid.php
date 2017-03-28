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

namespace Dravencms\AdminModule\Components\Form\SaveGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\Save;
use Dravencms\Model\Form\Repository\SaveRepository;
use Dravencms\Model\Form\Repository\SaveValueRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;

/**
 * Description of SaveGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SaveGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var SaveRepository */
    private $saveRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var SaveValueRepository */
    private $saveValueRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var Form */
    private $form;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * SaveGrid constructor.
     * @param Form $form
     * @param SaveRepository $saveRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     * @param CurrentLocale $currentLocale
     * @param SaveValueRepository $saveValueRepository
     */
    public function __construct(
        Form $form,
        SaveRepository $saveRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        CurrentLocale $currentLocale,
        SaveValueRepository $saveValueRepository
    )
    {
        parent::__construct();

        $this->form = $form;
        $this->baseGridFactory = $baseGridFactory;
        $this->saveRepository = $saveRepository;
        $this->entityManager = $entityManager;
        $this->saveValueRepository = $saveValueRepository;
        $this->currentLocale = $currentLocale;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->saveRepository->getSaveQueryBuilder($this->form));

        $grid->addColumnDate('createdAt', 'Created', $this->currentLocale->getDateTimeFormat());

        $grid->addColumnText('ip', 'IP')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnText('userAgent', 'User Agent')
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        foreach ($this->form->getItemGroups() AS $itemGroup)
        {
            foreach ($itemGroup->getItems() AS $item)
            {
                $grid->addColumnText('formItem_'.$item->getId(), $item->getName())
                    ->setCustomRender(function($row) use ($item){
                        /** @var Save $row */
                        $value = $this->saveValueRepository->getByItemAndSave($item, $row)->getValue();
                        if (in_array($item->getType(), [Item::TYPE_CHECKBOXLIST, Item::TYPE_MULTISELECT, Item::TYPE_SELECT, Item::TYPE_RADIOLIST]))
                        {
                            foreach($item->getItemOptions() AS $itemOption)
                            {
                                if ($itemOption->getId() == $value)
                                {
                                    return $itemOption->getIdentifier();
                                }
                            }

                            return 'Value not in list';
                        }
                        else
                        {
                            return $value;
                        }
                    });
            }
        }

        if ($this->presenter->isAllowed('form', 'delete')) {
            $grid->addActionHref('delete', 'Smazat', 'delete!')
                ->setCustomHref(function($row){
                    return $this->link('delete!', $row->getId());
                })
                ->setIcon('trash-o')
                ->setConfirm(function ($row) {
                    return ['Opravdu chcete smazat saved form %s ?', $row->getIp()];
                });


            $operations = ['delete' => 'Smazat'];
            $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
                ->setConfirm('delete', 'Opravu chcete smazat %i saved forms ?');
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
        $saves = $this->saveRepository->getById($id);
        foreach ($saves AS $save)
        {
            foreach ($save->getSaveValues() AS $saveValue)
            {
                $this->entityManager->remove($saveValue);
            }
            $this->entityManager->remove($save);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SaveGrid.latte');
        $template->render();
    }
}
