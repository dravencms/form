<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;

use Dravencms\AdminModule\Components\Form\SaveGrid\SaveGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Repository\FormRepository;

class SavePresenter extends SecuredPresenter
{
    /** @var FormRepository @inject */
    public $formRepository;

    /** @var SaveGridFactory @inject */
    public $saveGridFactory;

    /** @var Form */
    private $form = null;

    /**
     * @param integer $formId
     * @isAllowed(form,edit)
     */
    public function actionDefault($formId)
    {
        $this->form = $this->formRepository->getOneById($formId);
        $this->template->h1 = 'Forms';
    }

    /**
     * @return \Dravencms\AdminModule\Components\Form\SaveGrid\SaveGrid
     */
    public function createComponentGridSave()
    {
        $control = $this->saveGridFactory->create($this->form);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Saved form been successfully deleted', 'alert-success');
            $this->redirect('Save:', ['formId' => $this->form->getId()]);
        };
        return $control;
    }
}
