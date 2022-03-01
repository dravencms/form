<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Model\Form\Entities\Item;
use Dravencms\Model\Form\Entities\Save;
use Dravencms\Model\Form\Entities\SaveValue;
use Dravencms\Database\EntityManager;


class SaveValueRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|SaveValue */
    private $saveValueRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->saveValueRepository = $entityManager->getRepository(SaveValue::class);
    }

    /**
     * @param int $id
     * @return mixed|null|SaveValue
     */
    public function getOneById(int $id): ?SaveValue
    {
        return $this->saveValueRepository->find($id);
    }

    /**
     * @param $id
     * @return SaveValue[]
     */
    public function getById($id)
    {
        return $this->saveValueRepository->findBy(['id' => $id]);
    }

    /**
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getSaveQueryBuilder()
    {
        $qb = $this->saveValueRepository->createQueryBuilder('sv')
            ->select('sv');
        return $qb;
    }

    /**
     * @param $name
     * @param SaveValue|null $saveValueIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, SaveValue $saveValueIgnore = null): bool
    {
        $qb = $this->saveValueRepository->createQueryBuilder('sv')
            ->select('sv')
            ->where('sv.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($saveValueIgnore)
        {
            $qb->andWhere('sv != :saveValueIgnore')
                ->setParameter('saveValueIgnore', $saveValueIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

    /**
     * @param Item $item
     * @param Save $save
     * @return null|SaveValue
     */
    public function getByItemAndSave(Item $item, Save $save): ?SaveValue
    {
        return $this->saveValueRepository->findOneBy(['item' => $item, 'save' => $save]);
    }
}