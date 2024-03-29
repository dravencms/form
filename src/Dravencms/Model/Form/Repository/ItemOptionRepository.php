<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemOption;
use Dravencms\Model\Form\Entities\ItemOptionTranslation;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;

/**
 * Class ItemOptionRepository
 * @package App\Model\Form\Repository
 */
class ItemOptionRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|ItemOption */
    private $itemOptionRepository;

    /** @var \Doctrine\Persistence\ObjectRepository|ItemOptionTranslation */
    private $itemOptionTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * ItemOptionRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->itemOptionRepository = $entityManager->getRepository(ItemOption::class);
        $this->itemOptionTranslationRepository = $entityManager->getRepository(ItemOptionTranslation::class);
    }

    /**
     * @param $id
     * @return null|ItemOption
     */
    public function getOneById(int $id): ?ItemOption
    {
        return $this->itemOptionRepository->find($id);
    }

    /**
     * @param $id
     * @return ItemOption[]
     */
    public function getById($id)
    {
        return $this->itemOptionRepository->findBy(['id' => $id]);
    }

    /**
     * @param Item $item
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getItemOptionQueryBuilder(Item $item)
    {
        $qb = $this->itemOptionRepository->createQueryBuilder('io')
            ->select('io')
            ->where('io.item = :item')
            ->setParameter('item', $item);
        return $qb;
    }

    /**
     * @param $identifier
     * @param Item $item
     * @param ItemOption|null $itemOptionIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree(string $identifier, Item $item, ItemOption $itemOptionIgnore = null): bool
    {
        $qb = $this->itemOptionRepository->createQueryBuilder('io')
            ->select('io')
            ->where('io.identifier = :identifier')
            ->andWhere('io.item = :item')
            ->setParameters([
                'identifier' => $identifier,
                'item' => $item,
            ]);

        if ($itemOptionIgnore)
        {
            $qb->andWhere('io != :itemOptionIgnore')
                ->setParameter('itemOptionIgnore', $itemOptionIgnore);
        }

        $query = $qb->getQuery();
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param Item $item
     * @param ItemOption|null $itemOptionIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, ILocale $locale, Item $item, ItemOption $itemOptionIgnore = null): bool
    {
        $qb = $this->itemOptionRepository->createQueryBuilder('io')
            ->select('io')
            ->join('io.translations', 't')
            ->where('t.name = :name')
            ->andWhere('io.item = :item')
            ->andWhere('t.locale = :locale')
            ->setParameters([
                'name' => $name,
                'item' => $item,
                'locale' => $locale
            ]);

        if ($itemOptionIgnore)
        {
            $qb->andWhere('io != :itemOptionIgnore')
                ->setParameter('itemOptionIgnore', $itemOptionIgnore);
        }

        $query = $qb->getQuery();
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param ItemOption $itemOption
     * @param ILocale $locale
     * @return ItemOptionTranslation
     */
    public function getTranslation(ItemOption $itemOption, ILocale $locale): ?ItemOptionTranslation
    {
        $qb = $this->itemOptionTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->where('t.locale = :locale')
            ->andWhere('t.itemOption = :itemOption')
            ->setParameters([
                'itemOption' => $itemOption,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}