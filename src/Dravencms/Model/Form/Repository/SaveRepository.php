<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Form\Repository;

use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\Save;
use Kdyby\Doctrine\EntityManager;
use Nette;

class SaveRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $saveRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->saveRepository = $entityManager->getRepository(Save::class);
    }

    /**
     * @param $id
     * @return mixed|null|Save
     */
    public function getOneById($id)
    {
        return $this->saveRepository->find($id);
    }

    /**
     * @param $id
     * @return Save[]
     */
    public function getById($id)
    {
        return $this->saveRepository->findBy(['id' => $id]);
    }

    /**
     * @param Form $form
     * @return \Kdyby\Doctrine\QueryBuilder
     */
    public function getSaveQueryBuilder(Form $form)
    {
        $qb = $this->saveRepository->createQueryBuilder('s')
            ->select('s')
            ->join('s.saveValues', 'sv')
            ->join('sv.item', 'i')
            ->join('i.itemGroup', 'ig')
            ->join('ig.form', 'f')
            ->where('f = :form')
            ->setParameter('form', $form);
        return $qb;
    }

    /**
     * @param $name
     * @param Save|null $saveIgnore
     * @return boolean
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, Save $saveIgnore = null)
    {
        $qb = $this->saveRepository->createQueryBuilder('s')
            ->select('s')
            ->where('s.name = :name')
            ->setParameters([
                'name' => $name
            ]);

        if ($saveIgnore)
        {
            $qb->andWhere('s != :saveIgnore')
                ->setParameter('saveIgnore', $saveIgnore);
        }

        return (is_null($qb->getQuery()->getOneOrNullResult()));
    }

}