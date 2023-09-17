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

namespace Dravencms\AdminModule\Components\Form\ItemOptionGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Database\EntityManager;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Security\User;

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
    
    /** @var User */
    private $user;

    /**
     * @var array
     */
    public $onDelete = [];

    /**
     * ItemOptionGrid constructor.
     * @param Item $item
     * @param ItemOptionRepository $itemOptionRepository
     * @param BaseGridFactory $baseGridFactory
     * @param User $user
     * @param EntityManager $entityManager
     */
    public function __construct(
            Item $item,
            ItemOptionRepository $itemOptionRepository,
            BaseGridFactory $baseGridFactory,
            User $user,
            EntityManager $entityManager
            )
    {
        $this->item = $item;
        $this->user = $user;
        $this->baseGridFactory = $baseGridFactory;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @param string $name
     * @return Grid
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->itemOptionRepository->getItemOptionQueryBuilder($this->item));
        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('identifier', 'form.itemOption.identifier')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnPosition('position', 'form.itemOption.position', 'up!', 'down!');

        if ($this->user->isAllowed('form', 'edit'))
        {
            $grid->addAction('edit', '', 'ItemOption:edit', ['itemId' => 'item.id', 'id'])
                ->setIcon('pencil')
                ->setTitle('form.global.edit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('form.global.delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('form.global.doYouReallyWantToDeleteRow', 'identifier'));

            $grid->addGroupAction('form.global.delete')->onSelect[] = [$this, 'handleDelete'];
        }

        $grid->addExportCsvFiltered('form.global.csvExportFiltered', 'acl_resource_filtered.csv')
            ->setTitle('form.global.csvExportFiltered');

        $grid->addExportCsv('form.global.csvExport', 'acl_resource_all.csv')
            ->setTitle('form.global.csvExport');

        return $grid;
    }


    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(form, edit)
     */
    public function handleDelete($id): void
    {
        $itemOptions = $this->itemOptionRepository->getById($id);
        foreach ($itemOptions AS $itemOption)
        {
            $this->entityManager->remove($itemOption);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function handleUp(int $id): void
    {
        $item = $this->itemOptionRepository->getOneById($id);
        $item->setPosition($item->getPosition() - 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function handleDown(int $id): void
    {
        $item = $this->itemOptionRepository->getOneById($id);
        $item->setPosition($item->getPosition() + 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemOptionGrid.latte');
        $template->render();
    }
}
