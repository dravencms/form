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

namespace Dravencms\AdminModule\Components\Form\ItemForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemTranslantion;
use Dravencms\Model\Form\Repository\ItemRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Dravencms\Components\BaseForm\Form;
use Nette\Security\User;

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
    
    /** @var User */
    private $user;

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
     * @param User $user
     * @param Item|null $item
     */
    public function __construct(
        ItemGroup $itemGroup,
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        ItemRepository $itemRepository,
        LocaleRepository $localeRepository,
        User $user,
        Item $item = null
    ) {
        $this->item = $item;
        $this->itemGroup = $itemGroup;
        $this->user = $user;

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
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('defaultValue')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'form.item.defaultValueIsTooLong', 255);

            $container->addText('placeholder')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'form.item.placeholderIsTooLong', 255);

            $container->addText('title')
                ->setRequired(true)
                ->addRule(Form::MAX_LENGTH, 'form.item.titleIsTooLong', 255);

            $container->addText('required')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'form.item.requiredTextIsTooLong', 255);
        }

        $form->addText('name')
            ->setRequired('form.item.pleaseEnterFormItemName')
            ->addRule(Form::MAX_LENGTH, 'form.item.formItemNameIsTooLong', 255);

        $form->addSelect('type', null, Item::$typeList);

        $form->addInteger('minValue')
            ->setType('number');

        $form->addInteger('maxValue')
            ->setType('number');

        $form->addInteger('position')
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
    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();
        
        if (!$this->itemRepository->isNameFree($values->name, $this->itemGroup, $this->item)) {
            $form->addError('form.item.thisNameIsAlreadyTaken');
        }

        if (!$this->user->isAllowed('form', 'edit')) {
            $form->addError('form.item.youHaveNoPermissionToEditFormItem');
        }
    }

    /**
     * @param Form $item
     * @throws \Exception
     */
    public function editFormSucceeded(Form $item): void
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

    public function render(): void
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/ItemForm.latte');
        $template->render();
    }
}