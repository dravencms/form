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

namespace Dravencms\AdminModule\Components\Form\FormForm;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\FormTranslation;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Dravencms\Components\BaseForm\Form as AForm;

/**
 * Description of FormForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class FormForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var FormRepository */
    private $formRepository;

    /** @var LocaleRepository */
    private $localeRepository;
    
    /** @var User */
    private $user;

    /** @var Form|null */
    private $form = null;

    /** @var array */
    public $onSuccess = [];

    /**
     * FormForm constructor.
     * @param BaseFormFactory $baseFormFactory
     * @param EntityManager $entityManager
     * @param FormRepository $formRepository
     * @param LocaleRepository $localeRepository
     * @param Form|null $form
     */
    public function __construct(
        BaseFormFactory $baseFormFactory,
        EntityManager $entityManager,
        User $user,
        FormRepository $formRepository,
        LocaleRepository $localeRepository,
        Form $form = null
    ) {
        $this->form = $form;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->formRepository = $formRepository;
        $this->localeRepository = $localeRepository;

        if ($this->form) {
            $defaults = [
                'name' => $this->form->getName(),
                'email' => $this->form->getEmail(),
                'hookUrl' => $this->form->getHookUrl(),
                'isActive' => $this->form->isActive(),
                'isAntispam' => $this->form->isAntispam(),
                'isSaveToDatabase' => $this->form->isSaveToDatabase(),
            ];

            foreach ($this->form->getTranslations() AS $translation)
            {
                $defaults[$translation->getLocale()->getLanguageCode()]['sendButtonValue'] = $translation->getSendButtonValue();
                $defaults[$translation->getLocale()->getLanguageCode()]['successMessage'] = $translation->getSuccessMessage();
                $defaults[$translation->getLocale()->getLanguageCode()]['latteTemplate'] = $translation->getLatteTemplate();
            }
        }
        else{
            $defaults = [
                'isActive' => true,
                'isSaveToDatabase' => true
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

        $form->addText('name')
            ->setRequired('Please enter form name.')
            ->addRule(AForm::MAX_LENGTH, 'Form name is too long.', 255);

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('sendButtonValue')
                ->setRequired('Please enter send button text.')
                ->addRule(AForm::MAX_LENGTH, 'Send button text is too long.', 255);

            $container->addTextArea('successMessage')
                ->setRequired('Please enter send success message.')
                ->addRule(AForm::MAX_LENGTH, 'Success message is too long.', 2000);

            $container->addTextArea('latteTemplate');
        }

        $form->addText('email')
            ->setRequired(false)
            ->addRule(AForm::MAX_LENGTH, 'Form target emails field is too long.', 255);

        $form->addText('hookUrl')
            ->setRequired(false)
            ->addRule(AForm::URL, 'Hook URL must be valid URL');


        $form->addCheckbox('isActive');
        $form->addCheckbox('isAntispam');
        $form->addCheckbox('isSaveToDatabase');

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
        if (!$this->formRepository->isNameFree($values->name, $this->form)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->user->isAllowed('form', 'edit')) {
            $form->addError('Nemáte oprávění editovat article.');
        }
    }

    /**
     * @param AForm $form
     * @throws \Exception
     */
    public function editFormSucceeded(AForm $form): void
    {
        $values = $form->getValues();

        if ($this->form) {
            $form = $this->form;
            $form->setName($values->name);
            $form->setEmail($values->email);
            $form->setIsActive($values->isActive);
            $form->setIsAntispam($values->isAntispam);
            $form->setHookUrl($values->hookUrl);
            $form->setIsSaveToDatabase($values->isSaveToDatabase);
        } else {
            $form = new Form(
                $values->name,
                $values->email,
                $values->hookUrl,
                $values->isSaveToDatabase,
                $values->isActive,
                $values->isAntispam
            );
        }

        $this->entityManager->persist($form);
        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($formTranslation = $this->formRepository->getTranslation($form, $activeLocale))
            {
                $formTranslation->setLatteTemplate($values->{$activeLocale->getLanguageCode()}->latteTemplate);
                $formTranslation->setSendButtonValue($values->{$activeLocale->getLanguageCode()}->sendButtonValue);
                $formTranslation->setSuccessMessage($values->{$activeLocale->getLanguageCode()}->successMessage);
            }
            else
            {
                $formTranslation = new FormTranslation(
                    $form,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->sendButtonValue,
                    $values->{$activeLocale->getLanguageCode()}->successMessage,
                    $values->{$activeLocale->getLanguageCode()}->latteTemplate
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
        $template->setFile(__DIR__ . '/FormForm.latte');
        $template->render();
    }
}