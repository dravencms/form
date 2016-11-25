<?php

namespace Dravencms\Form\Script;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PostInstall implements IScript
{
    private $menuRepository;
    private $entityManager;

    public function __construct(MenuRepository $menuRepository, EntityManager $entityManager)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    public function run(IPackage $package)
    {
        $aclResource = new AclResource('form', 'Form');

        $this->entityManager->persist($aclResource);

        $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of form');
        $this->entityManager->persist($aclOperationEdit);
        $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of form');
        $this->entityManager->persist($aclOperationDelete);

        $adminMenu = new Menu('Forms', ':Admin:Form:Form', 'fa-credit-card', $aclOperationEdit);

        $foundRoot = $this->menuRepository->getOneByName('Site items');

        if ($foundRoot)
        {
            $this->menuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $foundRoot);
        }
        else
        {
            $this->entityManager->persist($adminMenu);
        }

        $this->entityManager->flush();

    }
}