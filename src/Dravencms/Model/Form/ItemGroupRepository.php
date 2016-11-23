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

class ItemGroupRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $itemGroupRepository;

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
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getItemGroupQueryBuilder(Form $form)
    {
        $qb = $this->itemGroupRepository->createQueryBuilder('ig')
            ->select('ig')
            ->where('ig.form = :form')
            ->setParameter('form', $form);
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
            ->where('ig.name = :name')
            ->andWhere('ig.form = :form')
            ->setParameters([
                'name' => $name,
                'form' => $form,
            ]);

        if ($itemGroupIgnore)
        {
            $qb->andWhere('ig != :itemGroupIgnore')
                ->setParameter('itemGroupIgnore', $itemGroupIgnore);
        }

        $query = $qb->getQuery();

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

}