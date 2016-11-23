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
use App\Model\Form\Entities\Form;
use App\Model\Form\Entities\Save;
use App\Model\Form\Repository\SaveRepository;
use App\Model\Form\Repository\SaveValueRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;

/**
 * Description of SaveGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SaveGrid extends Control
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var SaveRepository */
    private $saveRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var SaveValueRepository */
    private $saveValueRepository;

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
     * @param LocaleRepository $localeRepository
     * @param SaveValueRepository $saveValueRepository
     */
    public function __construct(Form $form, SaveRepository $saveRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager, LocaleRepository $localeRepository, SaveValueRepository $saveValueRepository)
    {
        parent::__construct();

        $this->form = $form;
        $this->baseGridFactory = $baseGridFactory;
        $this->saveRepository = $saveRepository;
        $this->entityManager = $entityManager;
        $this->localeRepository = $localeRepository;
        $this->saveValueRepository = $saveValueRepository;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid
     */
    public function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->saveRepository->getSaveQueryBuilder($this->form));

        $grid->addColumnDate('createdAt', 'Created', $this->localeRepository->getLocalizedDateTimeFormat());

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
                        return $this->saveValueRepository->getByItemAndSave($item, $row)->getValue();
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
