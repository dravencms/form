<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Model\Form\Entities\Form;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;

class FormCmsRepository implements ICmsComponentRepository
{
    /** @var FormRepository */
    private $formRepository;

    /**
     * FormCmsRepository constructor.
     * @param FormRepository $formRepository
     */
    public function __construct(FormRepository $formRepository)
    {
        $this->formRepository = $formRepository;
    }


    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions(string $componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
                $return = [];
                /** @var Form $form */
                foreach ($this->formRepository->getActive() AS $form) {
                    $return[] = new CmsActionOption($form->getName(), ['id' => $form->getId()]);
                }
                break;

            default:
                return false;
                break;
        }


        return $return;
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return null|CmsActionOption
     */
    public function getActionOption(string $componentAction, array $parameters)
    {
        $found = $this->formRepository->getOneByParameters($parameters);
        
        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}