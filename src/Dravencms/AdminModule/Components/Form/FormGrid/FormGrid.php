<?php declare(strict_types = 1);

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

namespace Dravencms\AdminModule\Components\Form\FormGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Database\EntityManager;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Security\User;

/**
 * Description of FormGrid
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class FormGrid extends BaseControl
{

    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var FormRepository */
    private $formRepository;

    /** @var EntityManager */
    private $entityManager;
    
    /** @var User */
    private $user;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * @param FormRepository $formRepository
     * @param BaseGridFactory $baseGridFactory
     * @param User $user
     * @param EntityManager $entityManager
     */
    public function __construct(
            FormRepository $formRepository,
            BaseGridFactory $baseGridFactory,
            User $user,
            EntityManager $entityManager
            )
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->formRepository = $formRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
    }


    /**
     * @param string $name
     * @return Grid
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->formRepository->getFormQueryBuilder());

        $grid->addColumnText('name', 'Name')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isActive', 'Active');

        if ($this->user->isAllowed('form', 'edit'))
        {
            $grid->addAction('itemGroup', 'Item groups', 'ItemGroup:', ['formId' => 'id'])
                ->setIcon('bars')
                ->setTitle('Item groups')
                ->setClass('btn btn-xs btn-default');

            $grid->addAction('savedData', 'Saved data', 'Save:', ['formId' => 'id'])
                ->setIcon('bars')
                ->setTitle('Saved data')
                ->setClass('btn btn-xs btn-default');


            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('Upravit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('Do you really want to delete row %s?', 'name'));

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
    public function gridGroupActionDelete(array $ids): void
    {
        $this->handleDelete($ids);
    }

    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(form, delete)
     */
    public function handleDelete($id): void
    {
        $forms = $this->formRepository->getById($id);
        foreach ($forms AS $form)
        {
            foreach ($form->getItemGroups() AS $itemGroup)
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
            $this->entityManager->remove($form);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/FormGrid.latte');
        $template->render();
    }
}
