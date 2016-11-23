<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\FormForm\FormFormFactory;
use Dravencms\AdminModule\Components\Form\FormGrid\FormGridFactory;
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

    /**
     * @isAllowed(form,edit)
     */
    public function renderDefault()
    {
        $this->template->h1 = 'Forms';
    }

    /**
     * @isAllowed(form,edit)
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($id = null)
    {
        if ($id) {
            $form = $this->formRepository->getOneById($id);

            if (!$form) {
                $this->error();
            }

            $this->form = $form;

            $this->template->h1 = 'Form edit';
        } else {
            $this->template->h1 = 'New form';
        }
    }

    /**
     * @return \AdminModule\Components\Form\FormForm
     */
    public function createComponentFormForm()
    {
        $control = $this->formFormFactory->create($this->form);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Form has been successfully saved', 'alert-success');
            $this->redirect('Form:');
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Form\FormGrid
     */
    public function createComponentGridForm()
    {
        $control = $this->formGridFactory->create();
        $control->onDelete[] = function()
        {
            $this->flashMessage('Form has been successfully deleted', 'alert-success');
            $this->redirect('Form:');
        };
        return $control;
    }
}
