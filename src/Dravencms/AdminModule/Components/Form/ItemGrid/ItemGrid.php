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

namespace Dravencms\AdminModule\Components\Form\ItemGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Security\User;

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
    
    /** @var User */
    private $user;

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
     * @param User $user
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param LocaleRepository $localeRepository
     */
    public function __construct(
        ItemGroup $itemGroup,
        ItemRepository $itemRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        User $user,
        CurrentLocaleResolver $currentLocaleResolver,
        LocaleRepository $localeRepository
    )
    {
        $this->itemGroup = $itemGroup;
        $this->baseGridFactory = $baseGridFactory;
        $this->itemRepository = $itemRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->localeRepository = $localeRepository;
    }


    /**
     * @param string $name
     * @return Grid
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->itemRepository->getItemQueryBuilder($this->itemGroup));
        $grid->setDefaultSort(['position' => 'ASC']);
        $grid->addColumnText('name', 'form.item.name')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('latteName', 'form.item.latteName')
            ->setRenderer(function($row){
                return 'formItem_'.$row->getId();
            });

        $grid->addColumnText('type', 'form.formItem.type')
            ->setRenderer(function ($row) {
                return Item::$typeList[$row->getType()];
            })
            ->setFilterText();

        $grid->addColumnPosition('position', 'form.item.position', 'up!', 'down!');

        $grid->addAction('itemOption', 'form.item.options', 'ItemOption:', ['itemId' => 'id'])
            ->setIcon('bars')
            ->setTitle('form.item.options')
            ->setClass('btn btn-xs btn-default');

        $grid->allowRowsAction('itemOption', function($item) {
            $multiOptions = [Item::TYPE_SELECT, Item::TYPE_MULTISELECT, Item::TYPE_RADIOLIST, Item::TYPE_CHECKBOXLIST];
            return $this->user->isAllowed('form', 'edit') && in_array($item->getType(), $multiOptions);
        });

        if ($this->user->isAllowed('form', 'edit'))
        {
            $grid->addAction('edit', '', 'Item:edit', ['itemGroupId' => 'itemGroup.id', 'id'])
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
                ->setConfirmation(new StringConfirmation('form.global.doYouReallyWantToDeleteRow', 'name'));

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
     * @isAllowed(form, delete)
     */
    public function handleDelete($id): void
    {
        $items = $this->itemRepository->getById($id);
        foreach ($items AS $item)
        {
            foreach ($item->getItemOptions() AS $itemOption)
            {
                $this->entityManager->remove($itemOption);
            }

            // Remove saved values
            foreach ($item->getSaveValues() AS $savedValue) 
            {
                $this->entityManager->remove($savedValue);
            }
  
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        $this->onDelete();
    }

    public function handleUp(int $id): void
    {
        $item = $this->itemRepository->getOneById($id);
        $item->setPosition($item->getPosition() - 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function handleDown(int $id): void
    {
        $item = $this->itemRepository->getOneById($id);
        $item->setPosition($item->getPosition() + 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }


    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGrid.latte');
        $template->render();
    }
}
