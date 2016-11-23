<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Form\Repository;

use App\Model\Form\Entities\Form;
use App\Model\Form\Entities\Item;
use App\Model\Form\Entities\Save;
use App\Model\Form\Entities\SaveValue;
use Kdyby\Doctrine\EntityManager;
use Nette;

class SaveValueRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
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
     * @param $id
     * @return mixed|null|SaveValue
     */
    public function getOneById($id)
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
    public function isNameFree($name, SaveValue $saveValueIgnore = null)
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
     * @return mixed|null|SaveValue
     */
    public function getByItemAndSave(Item $item, Save $save)
    {
        return $this->saveValueRepository->findOneBy(['item' => $item, 'save' => $save]);
    }
}