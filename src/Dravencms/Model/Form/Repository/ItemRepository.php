<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\ItemGroup;
use Dravencms\Model\Form\Entities\ItemTranslantion;
use Dravencms\Model\Locale\Entities\ILocale;
use Dravencms\Database\EntityManager;


class ItemRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Item */
    private $itemRepository;

    /** @var \Doctrine\Persistence\ObjectRepository|ItemTranslantion */
    private $itemTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->itemRepository = $entityManager->getRepository(Item::class);
        $this->itemTranslationRepository = $entityManager->getRepository(ItemTranslantion::class);
    }

    /**
     * @param $id
     * @return null|Item
     */
    public function getOneById(int $id): ?Item
    {
        return $this->itemRepository->find($id);
    }

    /**
     * @param $id
     * @return Item[]
     */
    public function getById($id)
    {
        return $this->itemRepository->findBy(['id' => $id]);
    }

    /**
     * @param ItemGroup $itemGroup
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getItemQueryBuilder(ItemGroup $itemGroup)
    {
        $qb = $this->itemRepository->createQueryBuilder('i')
            ->select('i')
            ->where('i.itemGroup = :itemGroup')
            ->setParameter('itemGroup', $itemGroup);
        return $qb;
    }

    /**
     * @param $name
     * @param ItemGroup $itemGroup
     * @param Item|null $itemIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, ItemGroup $itemGroup, Item $itemIgnore = null): bool
    {
        $qb = $this->itemRepository->createQueryBuilder('i')
            ->select('i')
            ->where('i.name = :name')
            ->andWhere('i.itemGroup = :itemGroup')
            ->setParameters([
                'name' => $name,
                'itemGroup' => $itemGroup
            ]);

        if ($itemIgnore)
        {
            $qb->andWhere('i != :itemIgnore')
                ->setParameter('itemIgnore', $itemIgnore);
        }

        $query = $qb->getQuery();

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Item $item
     * @param ILocale $locale
     * @return ItemTranslantion
     */
    public function getTranslation(Item $item, ILocale $locale): ?ItemTranslantion
    {
        $qb = $this->itemTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->where('t.locale = :locale')
            ->andWhere('t.item = :item')
            ->setParameters([
                'item' => $item,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }

}