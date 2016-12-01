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

class FormRepository
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
     * @return Form[]
     */
    public function getActive()
    {
        return $this->formRepository->findBy(['isActive' => true]);
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
}