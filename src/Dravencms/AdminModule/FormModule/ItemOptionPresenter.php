<?php declare(strict_types = 1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dravencms\AdminModule\FormModule;


use Dravencms\AdminModule\Components\Form\ItemOptionForm\ItemOptionFormFactory;
use Dravencms\AdminModule\Components\Form\ItemOptionForm\ItemOptionForm;
use Dravencms\AdminModule\Components\Form\ItemOptionGrid\ItemOptionGridFactory;
use Dravencms\AdminModule\Components\Form\ItemOptionGrid\ItemOptionGrid;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemOption;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Model\Form\Repository\ItemRepository;

/**
 * Class ItemOptionPresenter
 * @package AdminModule\FormModule
 */
class ItemOptionPresenter extends SecuredPresenter
{
    /** @var ItemRepository @inject */
    public $formItemRepository;

    /** @var ItemOptionRepository @inject */
    public $formItemOptionRepository;

    /** @var ItemOptionGridFactory @inject */
    public $itemOptionGridFactory;

    /** @var ItemOptionFormFactory @inject */
    public $itemOptionFormFactory;

    /** @var Item */
    private $item;

    /** @var ItemOption|null */
    private $itemOption = null;

    /**
     * @param integer $itemId
     * @isAllowed(form,edit)
     */
    public function actionDefault(int $itemId): void
    {
        $this->item = $this->formItemRepository->getOneById($itemId);
        $this->template->item = $this->item;
        $this->template->h1 = 'Item options';
    }
    
    /**
     * @isAllowed(form,edit)
     * @param integer $itemId
     * @param null $id
     * @throws \Nette\Application\BadRequestException
     */
    public function actionEdit(int $itemId, int $id = null): void
    {
        $this->item = $this->formItemRepository->getOneById($itemId);
        if ($id) {
            $itemOption = $this->formItemOptionRepository->getOneById($id);

            if (!$itemOption) {
                $this->error();
            }

            $this->itemOption = $itemOption;

            $this->template->h1 = 'Item option edit';
        } else {
            $this->template->h1 = 'New item option';
        }
    }

    /**
     * @return ItemOptionGrid
     */
    public function createComponentGridItemOption(): ItemOptionGrid
    {
        $control = $this->itemOptionGridFactory->create($this->item);
        $control->onDelete[] = function()
        {
            $this->flashMessage('Item option has been successfully deleted', 'alert-success');
            $this->redirect('ItemOption:', ['itemId' => $this->item->getId()]);
        };
        return $control;
    }

    /**
     * @return ItemOptionForm
     */
    public function createComponentFormItemOption(): ItemOptionForm
    {
        $control = $this->itemOptionFormFactory->create($this->item, $this->itemOption);
        $control->onSuccess[] = function()
        {
            $this->flashMessage('Item option has been successfully saved', 'alert-success');
            $this->redirect('ItemOption:', ['itemId' => $this->item->getId()]);
        };
        return $control;
    }
}
