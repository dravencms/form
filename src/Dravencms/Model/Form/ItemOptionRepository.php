<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Form\Repository;

use App\Model\Form\Entities\Item;
use App\Model\Form\Entities\ItemOption;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\Models\ILocale;

/**
 * Class ItemOptionRepository
 * @package App\Model\Form\Repository
 */
class ItemOptionRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemOptionRepository;

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
    }

    /**
     * @param $id
     * @return mixed|null|ItemOption
     */
    public function getOneById($id)
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
     * @param $name
     * @param ILocale $locale
     * @param Item $item
     * @param ItemOption|null $itemOptionIgnore
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, Item $item, ItemOption $itemOptionIgnore = null)
    {
        $qb = $this->itemOptionRepository->createQueryBuilder('io')
            ->select('io')
            ->where('io.name = :name')
            ->andWhere('io.item = :item')
            ->setParameters([
                'name' => $name,
                'item' => $item
            ]);

        if ($itemOptionIgnore)
        {
            $qb->andWhere('io != :itemOptionIgnore')
                ->setParameter('itemOptionIgnore', $itemOptionIgnore);
        }

        $query = $qb->getQuery();

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

}