<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\FormForm\FormFormFactory;
use Dravencms\AdminModule\Components\Form\FormForm\FormForm;
use Dravencms\AdminModule\Components\Form\FormGrid\FormGridFactory;
use Dravencms\AdminModule\Components\Form\FormGrid\FormGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Model\Form\Repository\ItemRepository;

class FormPresenter extends SecuredPresenter
{
    /** @var FormRepository @inject */
    public $formRepository;

    /** @var ItemRepository @inject */
    public $formItemRepository;

    /** @var FormFormFactory @inject */
    public $formFormFactory;

    /** @var FormGridFactory @inject */
    public $formGridFactory;

    /** @var Form|null */
    private $form = null;

    #[\Dravencms\User\Attributes\IsAllowed('form', 'edit')]
    public function renderDefault(): void
    {
        $this->template->h1 = $this->translator->translate('form.admin.title.forms');
    }

    /**
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    #[\Dravencms\User\Attributes\IsAllowed('form', 'edit')]
    public function actionEdit(?int $id = null): void
    {
        if ($id) {
            $form = $this->formRepository->getOneById($id);

            if (!$form) {
                $this->error();
            }

            $this->form = $form;

            $this->template->h1 = $this->translator->translate('form.admin.title.editForm');
        } else {
            $this->template->h1 = $this->translator->translate('form.admin.title.newForm');
        }
    }

    /**
     * @return FormForm
     */
    public function createComponentFormForm(): FormForm
    {
        $control = $this->formFormFactory->create($this->form);
        $control->onSuccess[] = function()
        {
            $this->flashMessage($this->translator->translate('form.admin.flash.formSaved'), 'alert-success');
            $this->redirect('Form:');
        };
        return $control;
    }

    /**
     * @return FormGrid
     */
    public function createComponentGridForm(): FormGrid
    {
        $control = $this->formGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('form.admin.flash.formDeleted'), 'alert-success');
            $this->redirect('Form:');
        };
        return $control;
    }
}
