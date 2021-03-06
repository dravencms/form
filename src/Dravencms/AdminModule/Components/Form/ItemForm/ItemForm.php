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

namespace Dravencms\AdminModule\Components\Form\ItemForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemTranslantion;
use Dravencms\Model\Form\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;

/**
 * Description of ItemForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemRepository */
    private $itemRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Item|null */
    private $item = null;

    /** @var ItemGroup */
    private $itemGroup;

    /** @var array */
    public $onSuccess = [];

    /**
     * ItemForm constructor.
     * @param ItemGroup $itemGroup
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param ItemRepository $itemRepository
     * @param LocaleRepository $localeRepository
     * @param Item|null $item
     */
    public function __construct(
        ItemGroup $itemGroup,
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ItemRepository $itemRepository,
        LocaleRepository $localeRepository,
        Item $item = null
    ) {
        parent::__construct();

        $this->item = $item;
        $this->itemGroup = $itemGroup;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->itemRepository = $itemRepository;
        $this->localeRepository = $localeRepository;

        if ($this->item) {
            $defaults = [
                'name' => $this->item->getName(),
                'type' => $this->item->getType(),
                'minValue' => $this->item->getMinValue(),
                'maxValue' => $this->item->getMaxValue(),
                'position' => $this->item->getPosition()
            ];

            foreach ($this->item->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['title'] = $translation->getTitle();
                $defaults[$translation->getLocale()->getLanguageCode()]['defaultValue'] = $translation->getDefaultValue();
                $defaults[$translation->getLocale()->getLanguageCode()]['placeholder'] = $translation->getPlaceholder();
                $defaults[$translation->getLocale()->getLanguageCode()]['required'] = $translation->getRequired();
            }
        }
        else{
            $defaults = [
                'isActive' => true
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return \Dravencms\Components\BaseForm\BaseForm
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('defaultValue')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Default value is too long.', 255);

            $container->addText('placeholder')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Placeholder is too long.', 255);

            $container->addText('title')
                ->setRequired(true)
                ->addRule(Form::MAX_LENGTH, 'Title is too long.', 255);

            $container->addText('required')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'Required text is too long.', 255);
        }

        $form->addText('name')
            ->setRequired('Please enter form name.')
            ->addRule(Form::MAX_LENGTH, 'Form name is too long.', 255);

        $form->addSelect('type', null, Item::$typeList);

        $form->addText('minValue')
            ->setType('number');

        $form->addText('maxValue')
            ->setType('number');

        $form->addText('position')
            ->setDisabled(is_null($this->item))
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
        
        if (!$this->itemRepository->isNameFree($values->name, $this->itemGroup, $this->item)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->presenter->isAllowed('form', 'edit')) {
            $form->addError('Nemáte oprávění editovat item.');
        }
    }

    /**
     * @param Form $item
     * @throws \Exception
     */
    public function editFormSucceeded(Form $item)
    {
        $values = $item->getValues();

        if ($this->item) {
            $item = $this->item;
            $item->setName($values->name);
            $item->setType($values->type);
            $item->setMinValue($values->minValue);
            $item->setMaxValue($values->maxValue);
            $item->setPosition($values->position);
        } else {
            $item = new Item($this->itemGroup, $values->name, $values->type, $values->minValue, $values->maxValue);
        }
        $this->entityManager->persist($item);

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($formTranslation = $this->itemRepository->getTranslation($item, $activeLocale))
            {
                $formTranslation->setTitle($values->{$activeLocale->getLanguageCode()}->title);
                $formTranslation->setDefaultValue($values->{$activeLocale->getLanguageCode()}->defaultValue);
                $formTranslation->setPlaceholder($values->{$activeLocale->getLanguageCode()}->placeholder);
                $formTranslation->setRequired($values->{$activeLocale->getLanguageCode()}->required);
            }
            else
            {
                $formTranslation = new ItemTranslantion(
                    $item,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->title,
                    $values->{$activeLocale->getLanguageCode()}->defaultValue,
                    $values->{$activeLocale->getLanguageCode()}->placeholder,
                    $values->{$activeLocale->getLanguageCode()}->required
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
        $template->setFile(__DIR__ . '/ItemForm.latte');
        $template->render();
    }
}