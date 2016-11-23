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

namespace Dravencms\AdminModule\Components\Form;

use Dravencms\Components\BaseFormFactory;
use App\Model\Form\Entities\Form;
use App\Model\Form\Repository\FormRepository;
use App\Model\Locale\Repository\LocaleRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form as NForm;

/**
 * Description of FormForm
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class FormForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var EntityManager */
    private $entityManager;

    /** @var FormRepository */
    private $formRepository;

    /** @var LocaleRepository */
    private $localeRepository;

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
        FormRepository $formRepository,
        LocaleRepository $localeRepository,
        Form $form = null
    ) {
        parent::__construct();

        $this->form = $form;

        $this->baseFormFactory = $baseFormFactory;
        $this->entityManager = $entityManager;
        $this->formRepository = $formRepository;
        $this->localeRepository = $localeRepository;

        if ($this->form) {
            $defaults = [
                'name' => $this->form->getName(),
                'email' => $this->form->getEmail(),
                /*'sendButtonValue' => $this->form->getSendButtonValue(),
                'successMessage' => $this->form->getSuccessMessage(),
                'latteTemplate' => $this->form->getLatteTemplate(),*/
                'isActive' => $this->form->isActive()
            ];

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaults += $repository->findTranslations($this->form);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale) {
                $defaults[$defaultLocale->getLanguageCode()]['sendButtonValue'] = $this->form->getSendButtonValue();
                $defaults[$defaultLocale->getLanguageCode()]['successMessage'] = $this->form->getSuccessMessage();
                $defaults[$defaultLocale->getLanguageCode()]['latteTemplate'] = $this->form->getLatteTemplate();
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

        $form->addText('name')
            ->setRequired('Please enter form name.')
            ->addRule(NForm::MAX_LENGTH, 'Form name is too long.', 255);

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('sendButtonValue')
                ->setRequired('Please enter send button text.')
                ->addRule(NForm::MAX_LENGTH, 'Send button text is too long.', 255);

            $container->addTextArea('successMessage')
                ->setRequired('Please enter send success message.')
                ->addRule(NForm::MAX_LENGTH, 'Success message is too long.', 2000);

            $container->addTextArea('latteTemplate');
        }

        $form->addText('email')
            ->setRequired(false)
            ->addRule(NForm::MAX_LENGTH, 'Form target emails field is too long.', 255);



        $form->addCheckbox('isActive');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param NForm $form
     */
    public function editFormValidate(NForm $form)
    {
        $values = $form->getValues();
        if (!$this->formRepository->isNameFree($values->name, $this->form)) {
            $form->addError('Tento název je již zabrán.');
        }

        if (!$this->presenter->isAllowed('form', 'edit')) {
            $form->addError('Nemáte oprávění editovat article.');
        }
    }

    /**
     * @param NForm $form
     * @throws \Exception
     */
    public function editFormSucceeded(NForm $form)
    {
        $values = $form->getValues();

        if ($this->form) {
            $form = $this->form;
            $form->setName($values->name);
            $form->setEmail($values->email);
            $form->setIsActive($values->isActive);
            /*$form->setSendButtonValue($values->sendButtonValue);
            $form->setSuccessMessage($values->successMessage);
            $form->setLatteTemplate($values->latteTemplate);*/
        } else {
            $defaultLocale = $this->localeRepository->getDefault();
            $form = new Form($values->name, $values->email, $values->{$defaultLocale->getLanguageCode()}->sendButtonValue, $values->{$defaultLocale->getLanguageCode()}->successMessage, $values->{$defaultLocale->getLanguageCode()}->latteTemplate, $values->isActive);
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($form, 'sendButtonValue', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->sendButtonValue)
                ->translate($form, 'successMessage', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->successMessage)
                ->translate($form, 'latteTemplate', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->latteTemplate);
        }

        $this->entityManager->persist($form);

        $this->entityManager->flush();

        $this->onSuccess();
    }

    public function render()
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/FormForm.latte');
        $template->render();
    }
}