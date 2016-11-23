<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\ItemFormFactory;
use Dravencms\AdminModule\Components\Form\ItemGridFactory;
use Dravencms\AdminModule\SecuredPresenter;
use App\Model\Form\Entities\Item;
use App\Model\Form\Entities\ItemGroup;
use App\Model\Form\Repository\ItemGroupRepository;
use App\Model\Form\Repository\ItemOptionRepository;
use App\Model\Form\Repository\ItemRepository;

class ItemPresenter extends SecuredPresenter
{
    /** @var ItemOptionRepository @inject */
    public $formItemOptionRepository;

    /** @var ItemGroupRepository @inject */
    public $itemGroupRepository;

    /** @var ItemRepository @inject */
    public $itemRepository;

    /** @var ItemGridFactory @inject */
    public $itemGridFactory;

    /** @var ItemFormFactory @inject */
    public $itemFormFactory;

    /** @var ItemGroup */
    private $itemGroup;

    /** @var Item|null */
    private $item = null;

    /**
     * @param integer $itemGroupId
     * @isAllowed(form,edit)
     */
    public function actionDefault($itemGroupId)
    {
        $this->itemGroup = $this->itemGroupRepository->getOneById($itemGroupId);
        $this->template->itemGroup = $this->itemGroup;
        $this->template->h1 = 'Items';
    }

    /**
     * @isAllowed(form,edit)
     * @param integer $itemGroupId
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit($itemGroupId, $id = null)
    {
        $this->itemGroup = $this->itemGroupRepository->getOneById($itemGroupId);

        if ($id) {
            $item = $this->itemRepository->getOneById($id);

            if (!$item) {
                $this->error();
            }

            $this->item = $item;

            $this->template->h1 = 'Item edit';
        } else {
            $this->template->h1 = 'New item';
        }
    }

    /**
     * @return \AdminModule\Components\Form\ItemGrid
     */
    public function createComponentGridItem()
    {
        $control = $this->itemGridFactory->create($this->itemGroup);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Item has been successfully deleted', 'alert-success');
            $this->redirect('Item:', ['itemGroupId' => $this->itemGroup->getId()]);
        };
        return $control;
    }

    /**
     * @return \AdminModule\Components\Form\ItemForm
     */
    public function createComponentFormItem()
    {
        $control = $this->itemFormFactory->create($this->itemGroup, $this->item);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Item has been successfully saved', 'alert-success');
            $this->redirect('Item:', ['itemGroupId' => $this->itemGroup->getId()]);
        };
        return $control;
    }
}
