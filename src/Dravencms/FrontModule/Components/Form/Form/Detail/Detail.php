<?php
namespace Dravencms\FrontModule\Components\Form\Form\Detail;


use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Flash;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Form\Entities\FormTranslation;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemOption;
use Dravencms\Model\Form\Entities\Save;
use Dravencms\Model\Form\Entities\SaveValue;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Model\Form\Repository\ItemRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Http\Request;
use Salamek\Cms\ICmsActionOption;
use Salamek\TemplatedEmail\TemplatedEmail;
use Tracy\Debugger;

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

    /** @var \Dravencms\Model\Form\Entities\Form|mixed|null */
    private $formInfo;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var ItemGroupRepository */
    private $itemGroupRepository;

    /** @var ItemRepository */
    private $itemRepository;

    /** @var FormTranslation */
    private $formInfoTranslation;

    /** @var ItemOptionRepository */
    private $itemOptionRepository;

    /**
     * Detail constructor.
     * @param ICmsActionOption $cmsActionOption
     * @param BaseFormFactory $baseFormFactory
     * @param FormRepository $formRepository
     * @param ItemGroupRepository $itemGroupRepository
     * @param ItemRepository $itemRepository
     * @param Request $request
     * @param EntityManager $entityManager
     * @param TemplatedEmail $templatedEmail
     * @param ItemOptionRepository $itemOptionRepository
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        ICmsActionOption $cmsActionOption,
        BaseFormFactory $baseFormFactory,
        FormRepository $formRepository,
        ItemGroupRepository $itemGroupRepository,
        ItemRepository $itemRepository,
        Request $request,
        EntityManager $entityManager,
        TemplatedEmail $templatedEmail,
        ItemOptionRepository $itemOptionRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->baseFormFactory = $baseFormFactory;
        $this->formRepository = $formRepository;
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->templatedEmail = $templatedEmail;
        $this->currentLocale = $currentLocale;
        $this->itemGroupRepository = $itemGroupRepository;
        $this->itemRepository = $itemRepository;
        $this->itemOptionRepository = $itemOptionRepository;

        $this->formInfo = $this->formRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $this->formInfoTranslation = $this->formRepository->getTranslation($this->formInfo, $this->currentLocale);
    }

    public function render()
    {
        $template = $this->template;

        $template->formInfo = $this->formInfo;

        if ($this->formInfoTranslation->getLatteTemplate())
        {
            //!FIXME HACK SPEED ISSUE
            $tmpfname = tempnam(sys_get_temp_dir(), 'detail.latte');
            file_put_contents($tmpfname, $this->formInfoTranslation->getLatteTemplate());
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
            $optionTranslation = $this->itemOptionRepository->getTranslation($itemOption, $this->currentLocale);
            $return[$itemOption->getId()] = $optionTranslation->getName();
        }

        return $return;
    }

    public function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->formInfo->getItemGroups() AS $formsItemsGroups) {
            
            if ($formsItemsGroups->isShowName())
            {
                $itemgGroupTranslation = $this->itemGroupRepository->getTranslation($formsItemsGroups, $this->currentLocale);
                $groupName = $itemgGroupTranslation->getName();
            }
            else
            {
                $groupName = null;
            }
            
            $form->addGroup($groupName);
            foreach ($formsItemsGroups->getItems() AS $formsItems) {
                $itemTranslation = $this->itemRepository->getTranslation($formsItems, $this->currentLocale);

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

                if ($itemTranslation->getRequired()) {
                    $objInput->setRequired($itemTranslation->getRequired());
                }

                if ($itemTranslation->getTitle()) {
                    $objInput->setAttribute('title', $itemTranslation->getTitle());
                }

                if ($itemTranslation->getPlaceholder()) {
                    $objInput->setAttribute('placeholder', $itemTranslation->getPlaceholder());
                }

                if ($itemTranslation->getDefaultValue() && !$form->isSubmitted()) {
                    $objInput->setValue($itemTranslation->getDefaultValue());
                }
            }
        }

        if ($this->formInfo->isAntispam())
        {
            $form->addReCaptcha();
        }

        $form->addSubmit('send', $this->formInfoTranslation->getSendButtonValue())
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

        $formData = [];
        foreach ($this->formInfo->getItemGroups() AS $formsItemsGroup) {
            foreach ($formsItemsGroup->getItems() AS $formsItem) {
                $formData[$formsItem->getName()] = $values->{'formItem_' . $formsItem->getId()};
                $saveValue = new SaveValue($values->{'formItem_' . $formsItem->getId()},$formsItem, $save);
                $this->entityManager->persist($saveValue);
            }
        }

        $this->entityManager->flush();

        if ($this->formInfo->getEmail())
        {
            $this->templatedEmail->formFormDetail([
                'title' => $this->formInfo->getName(),
                'formData' => $formData
            ])
                ->addTo($this->formInfo->getEmail())
                ->setSubject($this->formInfo->getName())
                ->send();
        }

        $this->presenter->flashMessage($this->formInfoTranslation->getSuccessMessage(), Flash::SUCCESS);
        $this->redirect('this');
    }
}
