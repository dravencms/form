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

namespace Dravencms\AdminModule\Components\Form\ItemGroupForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemGroupTranslation;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Dravencms\Components\BaseForm\Form as AForm;

/**
 * Description of FormForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class ItemGroupForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var ItemGroupRepository */
    private $itemGroupRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var Form */
    private $form;
    
    /** @var User */
    private $user;

    /** @var ItemGroup|null */
    private $itemGroup = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * ItemGroupForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param User $user
     * @param ItemGroupRepository $itemGroupRepository
     * @param LocaleRepository $localeRepository
     * @param Form $form
     * @param ItemGroup|null $itemGroup
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        User $user,
        ItemGroupRepository $itemGroupRepository,
        LocaleRepository $localeRepository,
        Form $form,
        ItemGroup $itemGroup = null
    ) {
        $this->form = $form;
        $this->itemGroup = $itemGroup;
        $this->user = $user;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->itemGroupRepository = $itemGroupRepository;
        $this->localeRepository = $localeRepository;

        if ($this->itemGroup) {
            $defaults = [
                'isShowName' => $this->itemGroup->isShowName(),
                'identifier' => $this->itemGroup->getIdentifier()
            ];

            foreach ($this->itemGroup->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
            }
        }
        else{
            $defaults = [
                'isShowName' => false
            ];
        }

        $this['form']->setDefaults($defaults);
    }

    /**
     * @return AForm
     */
    protected function createComponentForm(): AForm
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('form.itemGroup.pleaseEnterFormGroupName')
                ->addRule(AForm::MAX_LENGTH, 'form.itemGroup.formGroupNameIsTooLong', 255);
        }

        $form->addText('identifier');

        $form->addCheckbox('isShowName');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param AForm $form
     */
    public function editFormValidate(AForm $form): void
    {
        $values = $form->getValues();

        if (!$this->itemGroupRepository->isIdentifierFree($values->identifier, $this->form, $this->itemGroup))
        {
            $form->addError('form.itemGroup.thisIdentifierIsAlreadyTaken');
        }

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->itemGroupRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->form, $this->itemGroup)) {
                $form->addError('form.itemGroup.thisNameIsAlreadyTaken');
            }
        }

        if (!$this->user->isAllowed('form', 'edit')) {
            $form->addError('form.itemGroup.youHaveNoPermissionToEditFormGroup');
        }
    }

    /**
     * @param AForm $itemGroup
     * @throws \Exception
     */
    public function editFormSucceeded(AForm $itemGroup): void
    {
        $values = $itemGroup->getValues();

        if ($this->itemGroup) {
            $itemGroup = $this->itemGroup;
            $itemGroup->setIdentifier($values->identifier);
            $itemGroup->setIsShowName($values->isShowName);
        } else {
            $itemGroup = new ItemGroup($this->form, $values->identifier, $values->isShowName);
        }

        $this->entityManager->persist($itemGroup);
        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($formTranslation = $this->itemGroupRepository->getTranslation($itemGroup, $activeLocale))
            {
                $formTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
            }
            else
            {
                $formTranslation = new ItemGroupTranslation(
                    $itemGroup,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name
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
        $template->setFile(__DIR__ . '/ItemGroupForm.latte');
        $template->render();
    }
}