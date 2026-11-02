<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\ItemGroupForm\ItemGroupFormFactory;
use Dravencms\AdminModule\Components\Form\ItemGroupForm\ItemGroupForm;
use Dravencms\AdminModule\Components\Form\ItemGroupGrid\ItemGroupGridFactory;
use Dravencms\AdminModule\Components\Form\ItemGroupGrid\ItemGroupGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Model\Form\Repository\ItemGroupRepository;

class ItemGroupPresenter extends SecuredPresenter
{
    /** @var FormRepository @inject */
    public $formRepository;

    /** @var ItemGroupRepository @inject */
    public $itemGroupRepository;

    /** @var ItemGroupFormFactory @inject */
    public $itemGroupFormFactory;

    /** @var ItemGroupGridFactory @inject */
    public $itemGroupGridFactory;

    /** @var Form|null */
    private $form = null;

    /** @var ItemGroup|null */
    private $itemGroup = null;

    /**
     * @param integer $formId
     */
    #[\Dravencms\User\Attributes\IsAllowed('form', 'edit')]
    public function actionDefault(int $formId): void
    {
        $this->form = $this->formRepository->getOneById($formId);
        $this->template->form = $this->form;
        $this->template->h1 = $this->translator->translate('form.admin.title.groups');
    }

    /**
     * @param integer $formId
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    #[\Dravencms\User\Attributes\IsAllowed('form', 'edit')]
    public function actionEdit(int $formId, ?int $id = null): void
    {
        $this->form = $this->formRepository->getOneById($formId);

        if ($id) {
            $itemGroup = $this->itemGroupRepository->getOneById($id);

            if (!$itemGroup) {
                $this->error();
            }

            $this->itemGroup = $itemGroup;

            $this->template->h1 = $this->translator->translate('form.admin.title.editGroup');
        } else {
            $this->template->h1 = $this->translator->translate('form.admin.title.newGroup');
        }
    }

    /**
     * @return ItemGroupGrid
     */
    public function createComponentGridItemGroup(): ItemGroupGrid
    {
        $control = $this->itemGroupGridFactory->create($this->form);
        $control->onDelete[] = function()
        {
            $this->flashMessage($this->translator->translate('form.admin.flash.groupDeleted'), 'alert-success');
            $this->redirect('ItemGroup:', $this->form->getId());
        };
        return $control;
    }

    /**
     * @return ItemGroupForm
     */
    public function createComponentFormItemGroup(): ItemGroupForm
    {
        $control = $this->itemGroupFormFactory->create($this->form, $this->itemGroup);
        $control->onSuccess[] = function()
        {
            $this->flashMessage($this->translator->translate('form.admin.flash.groupSaved'), 'alert-success');
            $this->redirect('ItemGroup:', $this->form->getId());
        };
        return $control;
    }
}
