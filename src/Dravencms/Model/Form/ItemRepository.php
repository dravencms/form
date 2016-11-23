<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Form\Repository;

use App\Model\Form\Entities\Form;
use App\Model\Form\Entities\Item;
use App\Model\Form\Entities\ItemGroup;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\Models\ILocale;

class ItemRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemRepository;

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
    }

    /**
     * @param $id
     * @return mixed|null|Item
     */
    public function getOneById($id)
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
    public function isNameFree($name, ItemGroup $itemGroup, Item $itemIgnore = null)
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

}