<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\ItemForm\ItemFormFactory;
use Dravencms\AdminModule\Components\Form\ItemForm\ItemForm;
use Dravencms\AdminModule\Components\Form\ItemGrid\ItemGridFactory;
use Dravencms\AdminModule\Components\Form\ItemGrid\ItemGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Model\Form\Repository\ItemRepository;

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
    public function actionDefault(int $itemGroupId): void
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
    public function actionEdit(int $itemGroupId, int $id = null): void
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
     * @return ItemGrid
     */
    public function createComponentGridItem(): ItemGrid
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
     * @return ItemForm
     */
    public function createComponentFormItem(): ItemForm
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
