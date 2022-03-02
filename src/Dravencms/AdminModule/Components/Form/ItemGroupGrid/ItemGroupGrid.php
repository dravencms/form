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

namespace Dravencms\AdminModule\Components\Form\ItemGroupGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;

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
    
    /** @var User */
    private $user;

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
     * @param User $user
     */
    public function __construct(
        Form $form,
        ItemGroupRepository $itemGroupRepository,
        LocaleRepository $localeRepository,
        BaseGridFactory $baseGridFactory,
        CurrentLocaleResolver $currentLocaleResolver,
        EntityManager $entityManager,
        User $user
    )
    {
        $this->baseGridFactory = $baseGridFactory;
        $this->itemGroupRepository = $itemGroupRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->localeRepository = $localeRepository;
        $this->form = $form;
    }


    /**
     * @param string $name
     * @return Grid
     */
    public function createComponentGrid(string $name): Grid
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
        
        if ($this->user->isAllowed('form', 'edit')) {

            $grid->addAction('Item', 'Items', 'Item:', ['itemGroupId' => 'id'])
                ->setIcon('bars');

            $grid->addAction('edit', 'Upravit', 'ItemGroup:edit', ['formId' => 'form.id', 'id'])
                ->setIcon('pencil')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->user->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('Do you really want to delete row %s?', 'identifier'));

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
    public function handleDelete($id): void
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

    public function handleUp(int $id): void
    {
        $item = $this->itemGroupRepository->getOneById($id);
        $item->setPosition($item->getPosition() - 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    public function handleDown(int $id): void
    {
        $item = $this->itemGroupRepository->getOneById($id);
        $item->setPosition($item->getPosition() + 1);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }


    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/ItemGroupGrid.latte');
        $template->render();
    }
}
