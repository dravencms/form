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

use Dravencms\Components\BaseFormFactory;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\ItemRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * Description of ItemForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemForm extends Control
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
                //'title' => $this->item->getTitle(),
                'type' => $this->item->getType(),
                //'defaultValue' => $this->item->getDefaultValue(),
                'minValue' => $this->item->getMinValue(),
                'maxValue' => $this->item->getMaxValue(),
                //'placeholder' => $this->item->getPlaceholder(),
                //'required' => $this->item->getRequired(),
                'position' => $this->item->getPosition()
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->item);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['title'] = $this->item->getTitle();
                $defaults[$defaultLocale->getLanguageCode()]['defaultValue'] = $this->item->getDefaultValue();
                $defaults[$defaultLocale->getLanguageCode()]['placeholder'] = $this->item->getPlaceholder();
                $defaults[$defaultLocale->getLanguageCode()]['required'] = $this->item->getRequired();
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
     * @return \Dravencms\Components\BaseForm
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
            //$item->setTitle($values->title);
            $item->setType($values->type);
            //$item->setDefaultValue($values->defaultValue);
            $item->setMinValue($values->minValue);
            $item->setMaxValue($values->maxValue);
            //$item->setPlaceholder($values->placeholder);
            //$item->setRequired($values->required);
            $item->setPosition($values->position);
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $item = new Item($this->itemGroup, $values->name, $values->{$defaultLocale->getLanguageCode()}->title, $values->type, $values->{$defaultLocale->getLanguageCode()}->defaultValue, $values->minValue, $values->maxValue, $values->{$defaultLocale->getLanguageCode()}->placeholder, $values->{$defaultLocale->getLanguageCode()}->required);
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($item, 'title', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->title)
            ->translate($item, 'defaultValue', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->defaultValue)
            ->translate($item, 'placeholder', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->placeholder)
            ->translate($item, 'required', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->required);
        }

        $this->entityManager->persist($item);

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