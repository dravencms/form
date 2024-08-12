<?php declare(strict_types = 1);
namespace Dravencms\FrontModule\Components\Form\Form\Detail;


use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Flash;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Form\Entities\FormTranslation;
use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemOption;
use Dravencms\Model\Form\Entities\Save;
use Dravencms\Model\Form\Entities\SaveValue;
use Dravencms\Model\Form\Repository\FormRepository;
use Dravencms\Model\Form\Repository\ItemGroupRepository;
use Dravencms\Model\Form\Repository\ItemOptionRepository;
use Dravencms\Model\Form\Repository\ItemRepository;
use Dravencms\Database\EntityManager;
use Nette\Http\Request;
use Nette\Localization\Translator;
use Dravencms\Components\BaseForm\Form;
use Dravencms\Structure\ICmsActionOption;
use Salamek\TemplatedEmail\TemplatedEmail;
use Salamek\Tempnam\Tempnam;


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

    /** @var ILocale */
    private $currentLocale;

    /** @var ItemGroupRepository */
    private $itemGroupRepository;

    /** @var ItemRepository */
    private $itemRepository;

    /** @var FormTranslation */
    private $formInfoTranslation;

    /** @var ItemOptionRepository */
    private $itemOptionRepository;

    /** @var Tempnam */
    private $tempnam;

    /** @var Translator */
    private $translator;

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
     * @param CurrentLocaleResolver $currentLocaleResolver
     * @param Tempnam $tempnam
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
        CurrentLocaleResolver $currentLocaleResolver,
        Translator $translator,
        Tempnam $tempnam
    )
    {
        $this->cmsActionOption = $cmsActionOption;
        $this->baseFormFactory = $baseFormFactory;
        $this->formRepository = $formRepository;
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->templatedEmail = $templatedEmail;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
        $this->itemGroupRepository = $itemGroupRepository;
        $this->itemRepository = $itemRepository;
        $this->itemOptionRepository = $itemOptionRepository;
        $this->tempnam = $tempnam;
        $this->translator = $translator;

        $this->formInfo = $this->formRepository->getOneById($this->cmsActionOption->getParameter('id'));
        $this->formInfoTranslation = $this->formRepository->getTranslation($this->formInfo, $this->currentLocale);
    }

    public function render(): void
    {
        $template = $this->template;

        $template->formInfo = $this->formInfo;
        $template->currentLocale = $this->currentLocale;

        if ($this->formInfoTranslation->getLatteTemplate())
        {
            $key = __CLASS__ . $this->formInfoTranslation->getId();

            $tempFile = $this->tempnam->load($key, $this->formInfoTranslation->getUpdatedAt());

            if ($tempFile === null) {
                $tempFile = $this->tempnam->save($key, $this->formInfoTranslation->getLatteTemplate(), $this->formInfoTranslation->getUpdatedAt());
            }

            $template->setFile($tempFile);
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
    private function itemOptionsToArray($itemOptions): array
    {
        $return = [];
        foreach ($itemOptions AS $itemOption)
        {
            $optionTranslation = $this->itemOptionRepository->getTranslation($itemOption, $this->currentLocale);
            $return[$itemOption->getId()] = $optionTranslation->getName();
        }

        return $return;
    }

    /**
     * @return Form
     */
    public function createComponentForm(): Form
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
                            case Item::TYPE_NUMBER:
                                $objInput->addRule(Form::NUMERIC, 'You must enter an number');
                                if ($formsItems->getMinValue()) {
                                    $objInput->addRule(Form::MIN, 'Minimal number is %d', $formsItems->getMinValue());
                                }

                                if ($formsItems->getMaxValue()) {
                                    $objInput->addRule(Form::MAX, 'Maximal number is %d', $formsItems->getMaxValue());
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

                if ($itemTranslation->getDefaultValue()) {
                    $objInput->setValue($itemTranslation->getDefaultValue());
                }
            }
        }

        if ($this->formInfo->isAntispam())
        {
            $form->addCaptcha();
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
    public function mainFormValidate(Form $form): void
    {
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function mainFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        if ($this->formInfo->isSaveToDatabase())
        {
            $save = new Save($this->request->getRemoteAddress(), $this->request->getHeader('User-Agent'));

            $this->entityManager->persist($save);
        }

        $detectedEmails = [];
        $formData = [];
        $emailData = [];
        foreach ($this->formInfo->getItemGroups() AS $formsItemsGroup) {
            foreach ($formsItemsGroup->getItems() AS $formsItem) {
                $itemTranslation = $this->itemRepository->getTranslation($formsItem, $this->currentLocale);
                $value = $values->{'formItem_' . $formsItem->getId()};
                if (in_array($formsItem->getType() , [Item::TYPE_CHECKBOXLIST, Item::TYPE_MULTISELECT, Item::TYPE_SELECT, Item::TYPE_RADIOLIST]))
                {
                    foreach($formsItem->getItemOptions() AS $itemOption)
                    {
                        if ($itemOption->getId() == $value)
                        {
                            $formData[$formsItem->getName()] = $itemOption->getIdentifier();
                            $emailData[$itemTranslation->getTitle()] = $itemOption->getIdentifier();
                            break;
                        }
                    }
                }
                else
                {
                    if ($formsItem->getType() == Item::TYPE_EMAIL)
                    {
                        $detectedEmails[] = $value;
                    }
                    $formData[$formsItem->getName()] = $value;

                    if (in_array($formsItem->getType() , [Item::TYPE_CHECKBOX])) {
                        $emailData[$itemTranslation->getTitle()] = $this->translator->translate($value ? 'form.email.yes' : 'form.email.no');
                    } else {
                        $emailData[$itemTranslation->getTitle()] = $value;
                    }
                }

                if ($this->formInfo->isSaveToDatabase()) {
                    $saveValue = new SaveValue((string)$value, $formsItem, $save);
                    $this->entityManager->persist($saveValue);
                }
            }
        }

        if ($this->formInfo->isSaveToDatabase()) {
            $this->entityManager->flush();
        }

        if ($this->formInfo->getEmail() || $this->formInfo->isSendFormToDetectedEmail())
        {
            // Are we sending
            $mailSend = $this->templatedEmail->formFormDetail([
                'name' => $this->formInfo->getName(),
                'emailData' => $emailData
            ])
                ->setSubject($this->formInfo->getName());

            if ($this->formInfo->isSendFormToDetectedEmail() && $this->formInfo->getEmail())
            {
                // Sending to both
                $mailSend->addBcc($this->formInfo->getEmail());
                foreach($detectedEmails AS $detectedEmail)
                {
                    $mailSend->addTo($detectedEmail);
                }
                
            } else if($this->formInfo->isSendFormToDetectedEmail()) {
                // Sending only to detected email
                foreach($detectedEmails AS $detectedEmail)
                {
                    $mailSend->addTo($detectedEmail);
                }
            } else {
                // Sending only to specified email
                $mailSend->addTo($this->formInfo->getEmail());
            }

            $mailSend->send();
        }

        if ($this->formInfo->getHookUrl())
        {
            $data = [
                'id' => $this->formInfo->getId(),
                'title' => $this->formInfo->getName(),
                'locale' => $this->currentLocale->getLanguageCode(),
                'emails' => $detectedEmails,
                'formData' => $formData
            ];
            $opts = [
                'http' =>
                    [
                        'method' => 'POST',
                        'header' => 'Content-type: application/json',
                        'content' => json_encode($data),
                        'ignore_errors' => true
                    ]
            ];

            $context = stream_context_create($opts);

            @file_get_contents($this->formInfo->getHookUrl(), false, $context);
        }

        $this->presenter->flashMessage($this->formInfoTranslation->getSuccessMessage(), Flash::SUCCESS);
        $this->redirect('this');
    }
}
