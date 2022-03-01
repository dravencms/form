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

namespace Dravencms\AdminModule\Components\Form\SaveGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\Save;
use Dravencms\Model\Form\Repository\SaveRepository;
use Dravencms\Model\Form\Repository\SaveValueRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;

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

    /** @var ILocale */
    private $currentLocale;

    /** @var User */
    private $user;
    
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
     * @param User $user
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param SaveValueRepository $saveValueRepository
     */
    public function __construct(
        Form $form,
        SaveRepository $saveRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager,
        User $user,
        CurrentLocaleResolver $currentLocaleResolver,
        SaveValueRepository $saveValueRepository
    )
    {
        $this->form = $form;
        $this->baseGridFactory = $baseGridFactory;
        $this->saveRepository = $saveRepository;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->saveValueRepository = $saveValueRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }


    /**
     * @param string $name
     * @return Grid
     */
    public function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->saveRepository->getSaveQueryBuilder($this->form));

        $grid->addColumnDateTime('createdAt', 'Created')
            ->setFormat($this->currentLocale->getDateTimeFormat());

        $grid->addColumnText('ip', 'IP')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('userAgent', 'User Agent')
            ->setSortable()
            ->setFilterText();

        foreach ($this->form->getItemGroups() AS $itemGroup)
        {
            foreach ($itemGroup->getItems() AS $item)
            {
                $grid->addColumnText('formItem_'.$item->getId(), $item->getName())
                    ->setRenderer(function($row) use ($item){
                        /** @var Save $row */
                        $dataRow = $this->saveValueRepository->getByItemAndSave($item, $row);
                        if (!$dataRow) {
                            return '?';
                        }
                        $value = $dataRow->getValue();
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

        if ($this->user->isAllowed('form', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirmation(new StringConfirmation('Do you really want to delete row %s?', 'identifier'));

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

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/SaveGrid.latte');
        $template->render();
    }
}
