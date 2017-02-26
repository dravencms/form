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

namespace Dravencms\AdminModule\Components\Form\ItemOptionForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemOption;
use Dravencms\Model\Form\Entities\ItemOptionTranslation;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;

/**
 * Description of ItemOptionForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemOptionForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemOptionRepository */
    private $itemOptionRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var ItemOption|null */
    private $itemOption = null;

    /** @var Item */
    private $item;

    /** @var array */
    public $onSuccess = [];

    /**
     * ItemOptionForm constructor.
     * @param Item $item
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param ItemOptionRepository $itemOptionRepository
     * @param LocaleRepository $localeRepository
     * @param ItemOption|null $itemOption
     */
    public function __construct(
        Item $item,
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ItemOptionRepository $itemOptionRepository,
        LocaleRepository $localeRepository,
        ItemOption $itemOption = null
    ) {
        parent::__construct();

        $this->itemOption = $itemOption;
        $this->item = $item;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->localeRepository = $localeRepository;

        if ($this->itemOption) {
            $defaults = [
                'position' => $this->itemOption->getPosition()
            ];

            foreach ($this->itemOption->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
            }

            $this['form']->setDefaults($defaults);
        }
    }

    /**
     * @return \Dravencms\Components\BaseForm\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());
            $container->addText('name')
                ->setRequired('Please enter form name.')
                ->addRule(Form::MAX_LENGTH, 'Form name is too long.', 255);
        }
        
        $form->addText('position')
            ->setDisabled(is_null($this->itemOption))
            ->setType('number');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();
        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->itemOptionRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->item, $this->itemOption)) {
                $form->addError('Tento název je již zabrán.');
            }
        }

        if (!$this->presenter->isAllowed('form', 'edit')) {
            $form->addError('Nemáte oprávění editovat item option.');
        }
    }

    /**
     * @param Form $itemOption
     * @throws \Exception
     */
    public function editFormSucceeded(Form $itemOption)
    {
        $values = $itemOption->getValues();

        if ($this->itemOption) {
            $itemOption = $this->itemOption;
            $itemOption->setPosition($values->position);
        } else {
            $itemOption = new ItemOption($this->item);
        }

        $this->entityManager->persist($itemOption);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($formTranslation = $this->itemOptionRepository->getTranslation($itemOption, $activeLocale))
            {
                $formTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
            }
            else
            {
                $formTranslation = new ItemOptionTranslation(
                    $itemOption,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name
                );
            }

            $this->entityManager->persist($formTranslation);
        }

        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render()
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/ItemOptionForm.latte');
        $template->render();
    }
}