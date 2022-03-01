<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Doctrine\ORM\QueryBuilder;
use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemGroupTranslation;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;

class ItemGroupRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|ItemGroup */
    private $itemGroupRepository;

    /** @var \Doctrine\Persistence\ObjectRepository|ItemGroupTranslation */
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
     * @param int $id
     * @return null|ItemGroup
     */
    public function getOneById(int $id): ?ItemGroup
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
     * @return QueryBuilder
     */
    public function getItemGroupQueryBuilder(Form $form)
    {
        $qb = $this->itemGroupRepository->createQueryBuilder('ig')
            ->select('ig')
            ->andWhere('ig.form = :form')
            ->setParameters([
                'form' => $form
            ]);
        return $qb;
    }

    /**
     * @param $identifier
     * @param Form $form
     * @param ItemGroup|null $itemGroupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree(string $identifier, Form $form, ItemGroup $itemGroupIgnore = null): bool
    {
        $qb = $this->itemGroupRepository->createQueryBuilder('ig')
            ->select('ig')
            ->where('ig.identifier = :identifier')
            ->andWhere('ig.form = :form')
            ->setParameters([
                'identifier' => $identifier,
                'form' => $form
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
     * @param $name
     * @param ILocale $locale
     * @param Form $form
     * @param ItemGroup|null $itemGroupIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, ILocale $locale, Form $form, ItemGroup $itemGroupIgnore = null): bool
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
     * @return ItemGroupTranslation
     */
    public function getTranslation(ItemGroup $itemGroup, ILocale $locale): ?ItemGroupTranslation
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