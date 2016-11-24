<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Locale\TLocalizedRepository;
use Dravencms\Model\Form\Entities\Form;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class FormRepository implements ICmsComponentRepository
{
    use TLocalizedRepository;

    /** @var \Kdyby\Doctrine\EntityRepository */
    private $formRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->formRepository = $entityManager->getRepository(Form::class);
    }

    /**
     * @param $id
     * @return mixed|null|Form
     */
    public function getOneById($id)
    {
        return $this->formRepository->find($id);
    }

    /**
     * @param $id
     * @return Form[]
     */
    public function getById($id)
    {
        return $this->formRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getFormQueryBuilder()
    {
        $qb = $this->formRepository->createQueryBuilder('f')
            ->select('f');
        return $qb;
    }

    /**
     * @param $name
     * @param Form|null $formIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Form $formIgnore = null)
    {
        $qb = $this->formRepository->createQueryBuilder('f')
            ->select('f')
            ->where('f.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($formIgnore)
        {
            $qb->andWhere('f != :formIgnore')
                ->setParameter('formIgnore', $formIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }


    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Detail':
                $return = [];
                /** @var Form $form */
                foreach ($this->formRepository->findBy(['isActive' => true]) AS $form) {
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
     * @param ILocale $locale
     * @return null|CmsActionOption
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        $found = $this->findTranslatedOneBy($this->formRepository, $locale, $parameters + ['isActive' => true]);

        if ($found)
        {
            return new CmsActionOption($found->getName(), $parameters);
        }

        return null;
    }
}