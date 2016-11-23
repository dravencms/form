<?php
namespace Dravencms\FrontModule\Components\Form\Form;
use Dravencms\Components\BaseControl;
use Dravencms\Components\BaseFormFactory;
use App\Model\Form\Entities\Item;
use App\Model\Form\Entities\ItemOption;
use App\Model\Form\Entities\Save;
use App\Model\Form\Entities\SaveValue;
use App\Model\Form\Repository\FormRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Salamek\Cms\ICmsActionOption;
use Salamek\TemplatedEmail\TemplatedEmail;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormPresenter
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class Detail extends BaseControl
{
    /** @var ICmsActionOption */
    private $cmsActionOption;

    /** @var FormRepository */
    private $formRepository;

    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var Request */
    private $request;

    /** @var EntityManager */
    private $entityManager;

    /** @var TemplatedEmail */
    private $templatedEmail;

    /** @var \App\Model\Form\Entities\Form|mixed|null */
    private $formInfo;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param BaseFormFactory $baseFormFactory
     * @param FormRepository $formRepository
     * @param Request $request
     * @param EntityManager $entityManager
     * @param TemplatedEmail $templatedEmail
     */
    public function __construct(ICmsActionOption $cmsActionOption, BaseFormFactory $baseFormFactory, FormRepository $formRepository, Request $request, EntityManager $entityManager, TemplatedEmail $templatedEmail)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->baseFormFactory = $baseFormFactory;
        $this->formRepository = $formRepository;
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->templatedEmail = $templatedEmail;

        $this->formInfo = $this->formRepository->getOneById($this->cmsActionOption->getParameter('id'));
    }

    public function render()
    {
        $template = $this->template;

        $template->formInfo = $this->formInfo;

        if ($this->formInfo->getLatteTemplate())
        {
            //!FIXME HACK SPEED ISSUE
            $tmpfname = tempnam(sys_get_temp_dir(), 'detail.latte');
            file_put_contents($tmpfname, $this->formInfo->getLatteTemplate());
            $template->setFile($tmpfname);
        }
        else
        {
            $template->setFile(__DIR__.'/detail.latte');
        }

        $template->render();
    }

    /**
     * @param ItemOption[] $itemOptions
     * @return array
     */
    private function itemOptionsToArray($itemOptions)
    {
        $return = [];
        foreach ($itemOptions AS $itemOption)
        {
            $return[$itemOption->getId()] = $itemOption->getName();
        }

        return $return;
    }

    public function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->formInfo->getItemGroups() AS $formsItemsGroups) {
            $form->addGroup(($formsItemsGroups->isShowName() ? $formsItemsGroups->getName() : null));
            foreach ($formsItemsGroups->getItems() AS $formsItems) {
                switch ($formsItems->getType()) {
                    case Item::TYPE_TEXT:
                    case Item::TYPE_NUMBER:
                    case Item::TYPE_EMAIL:
                    case Item::TYPE_TEL:
                    case Item::TYPE_URL:
                        $objInput = $form->addText('formItem_' . $formsItems->getId(), $formsItems->getName());

                        switch ($formsItems->getType()) {
                            case Item::TYPE_TEXT:
                                break;
                            case Item::TYPE_NUMBER:
                            case Item::TYPE_EMAIL:
                            case Item::TYPE_TEL:
                            case Item::TYPE_URL:
                                $objInput->setType($formsItems->getType());
                                break;
                        }

                        switch ($formsItems->getType()) {
                            case Item::TYPE_EMAIL:
                                $objInput->addRule(Form::EMAIL, 'Please enter valid email');
                            case Item::TYPE_TEL:
                            case Item::TYPE_TEXT:
                            case Item::TYPE_URL:
                                if ($formsItems->getMinValue()) {
                                    $objInput->addRule(Form::MIN_LENGTH, 'Input must have at least %d characters', $formsItems->getMinValue());
                                }

                                if ($formsItems->getMaxValue()) {
                                    $objInput->addRule(Form::MAX_LENGTH, 'Input must have maximaly %d characters', $formsItems->getMaxValue());
                                }

                                break;
                        }

                        break;
                    case Item::TYPE_SELECT:
                        $objInput = $form->addSelect('formItem_' . $formsItems->getId(), $formsItems->getName(), $this->itemOptionsToArray($formsItems->getItemOptions()));
                        break;
                    case Item::TYPE_MULTISELECT:
                        $objInput = $form->addMultiSelect('formItem_' . $formsItems->getId(), $formsItems->getName(), $this->itemOptionsToArray($formsItems->getItemOptions()));
                        break;
                    case Item::TYPE_RADIOLIST:
                        $objInput = $form->addRadioList('formItem_' . $formsItems->getId(), $formsItems->getName(), $this->itemOptionsToArray($formsItems->getItemOptions()));
                        break;
                    case Item::TYPE_CHECKBOXLIST:
                        $objInput = $form->addCheckboxList('formItem_' . $formsItems->getId(), $formsItems->getName(), $this->itemOptionsToArray($formsItems->getItemOptions()));
                        break;
                    case Item::TYPE_CHECKBOX:
                        $objInput = $form->addCheckbox('formItem_' . $formsItems->getId(), $formsItems->getName());
                        break;
                    case Item::TYPE_TEXTAREA:
                        $objInput = $form->addTextArea('formItem_' . $formsItems->getId(), $formsItems->getName());
                        break;
                    default:
                        $objInput = $form->addText('formItem_' . $formsItems->getId(), $formsItems->getName());
                        break;
                }

                $objInput->setAttribute('class', 'form-control');

                if ($formsItems->getRequired()) {
                    $objInput->setRequired($formsItems->getRequired());
                }

                if ($formsItems->getTitle()) {
                    $objInput->setAttribute('title', $formsItems->getTitle());
                }

                if ($formsItems->getPlaceholder()) {
                    $objInput->setAttribute('placeholder', $formsItems->getPlaceholder());
                }

                if ($formsItems->getDefaultValue() && !$form->isSubmitted()) {
                    $objInput->setValue($formsItems->getDefaultValue());
                }
            }
        }

        $form->addReCaptcha();

        $form->addSubmit('send', $this->formInfo->getSendButtonValue())
            ->setAttribute('class', 'btn btn-success');


        $form->onValidate[] = [$this, 'mainFormValidate'];
        $form->onSuccess[] = [$this, 'mainFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function mainFormValidate(Form $form)
    {
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function mainFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        $save = new Save($this->request->getRemoteAddress(), $this->request->getHeader('User-Agent'));

        $this->entityManager->persist($save);

        foreach ($this->formInfo->getItemGroups() AS $formsItemsGroup) {
            foreach ($formsItemsGroup->getItems() AS $formsItem) {
                $saveValue = new SaveValue($values->{'formItem_' . $formsItem->getId()},$formsItem,  $save);
                $this->entityManager->persist($saveValue);
            }
        }

        $this->entityManager->flush();

        if ($this->formInfo->getEmail())
        {
            $this->templatedEmail->formFormDetail([
                'title' => $this->formInfo->getName()
            ])
                ->addTo($this->formInfo->getEmail())
                ->setSubject($this->formInfo->getName())
                ->send();
        }

        $this->presenter->flashMessage($this->formInfo->getSuccessMessage(), 'alert-success');
        $this->redirect('this');
    }
}
