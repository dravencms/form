<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemGroupTranslation;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Dravencms\Model\Locale\Entities\ILocale;

class ItemGroupRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemGroupRepository;

    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemGroupTranslantionRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->itemGroupRepository = $entityManager->getRepository(ItemGroup::class);
        $this->itemGroupTranslantionRepository = $entityManager->getRepository(ItemGroupTranslation::class);
    }

    /**
     * @param $id
     * @return mixed|null|ItemGroup
     */
    public function getOneById($id)
    {
        return $this->itemGroupRepository->find($id);
    }

    /**
     * @param $id
     * @return ItemGroup[]
     */
    public function getById($id)
    {
        return $this->itemGroupRepository->findBy(['id' => $id]);
    }

    /**
     * @param Form $form
     * @param ILocale $locale
     * @return static
     */
    public function getItemGroupQueryBuilder(Form $form, ILocale $locale, ILocale $defaultLocale)
    {
        $qb = $this->itemGroupTranslantionRepository->createQueryBuilder('t')
            ->select('t')
            ->join('t.itemGroup', 'it')
            //->where('t.locale = :locale OR t.locale = :defaultLocale')
            ->andWhere('it.form = :form')
            ->groupBy('it')
            ->setParameters([
                'form' => $form,
                /*'locale' => $locale->getId(),
                'defaultLocale' => $defaultLocale*/
            ]);
        return $qb;
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Form $form
     * @param ItemGroup|null $itemGroupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Form $form, ItemGroup $itemGroupIgnore = null)
    {
        $qb = $this->itemGroupRepository->createQueryBuilder('ig')
            ->select('ig')
            ->join('ig.translations', 't')
            ->where('t.name = :name')
            ->andWhere('t.locale = :locale')
            ->andWhere('ig.form = :form')
            ->setParameters([
                'name' => $name,
                'form' => $form,
                'locale' => $locale
            ]);

        if ($itemGroupIgnore)
        {
            $qb->andWhere('ig != :itemGroupIgnore')
                ->setParameter('itemGroupIgnore', $itemGroupIgnore);
        }

        $query = $qb->getQuery();
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param ItemGroup $itemGroup
     * @param ILocale $locale
     * @return mixed
     */
    public function getTranslation(ItemGroup $itemGroup, ILocale $locale)
    {
        $qb = $this->itemGroupTranslantionRepository->createQueryBuilder('t')
            ->select('t')
            ->where('t.locale = :locale')
            ->andWhere('t.itemGroup = :itemGroup')
            ->setParameters([
                'itemGroup' => $itemGroup,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}