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
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Tracy\Debugger;

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

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Form */
    private $form;

    /** @var ILocale */
    private $currentLocale;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemGroupGrid constructor.
     * @param Form $form
     * @param ItemGroupRepository $itemGroupRepository
     * @param LocaleRepository $localeRepository
     * @param BaseGridFactory $baseGridFactory
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param EntityManager $entityManager
     */
    public function __construct(
        Form $form,
        ItemGroupRepository $itemGroupRepository,
        LocaleRepository $localeRepository,
        BaseGridFactory $baseGridFactory,
        CurrentLocaleResolver $currentLocaleResolver,
        EntityManager $entityManager
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->itemGroupRepository = $itemGroupRepository;
        $this->entityManager = $entityManager;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->localeRepository = $localeRepository;
        $this->form = $form;
    }


    /**
     * @param $name
     * @return \Dravencms\Components\BaseGrid\BaseGrid
     */
    public function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->itemGroupRepository->getItemGroupQueryBuilder($this->form));
        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'Identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnPosition('position', 'Position', 'up!', 'down!');

        $grid->addColumnBoolean('isShowName', 'Show name');
        
        if ($this->presenter->isAllowed('form', 'edit')) {

            $grid->addAction('Item', 'Items', 'Item:', ['itemGroupId' => 'id'])
                ->setIcon('bars');

            $grid->addAction('edit', 'Upravit', 'ItemGroup:edit', ['formId' => 'form.id', 'id'])
                ->setIcon('pencil')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'handleDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(form, delete)
     */
    public function handleDelete($id)
    {
        $itemGroups = $this->itemGroupRepository->getById($id);
        foreach ($itemGroups AS $itemGroup)
        {
            foreach ($itemGroup->getItems() AS $item)
            {
                foreach ($item->getItemOptions() AS $itemOption)
                {
                    $this->entityManager->remove($itemOption);
                }
                $this->entityManager->remove($item);
            }
            $this->entityManager->remove($itemGroup);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function handleUp($id)
    {
        $item = $this->itemGroupRepository->getOneById($id);
        $item->setPosition($item->getPosition() - 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function handleDown($id)
    {
        $item = $this->itemGroupRepository->getOneById($id);
        $item->setPosition($item->getPosition() + 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }


    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGroupGrid.latte');
        $template->render();
    }
}
