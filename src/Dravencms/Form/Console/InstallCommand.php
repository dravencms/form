<?php

namespace Dravencms\Form\Console;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('dravencms:form:install')
            ->setDescription('Installs dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MenuRepository $adminMenuRepository */
        $adminMenuRepository = $this->getHelper('container')->getByType('Dravencms\Model\Admin\Repository\MenuRepository');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getHelper('container')->getByType('Kdyby\Doctrine\EntityManager');

        try {

            $aclResource = new AclResource('form', 'Form');

            $entityManager->persist($aclResource);

            $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of form');
            $entityManager->persist($aclOperationEdit);
            $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of form');
            $entityManager->persist($aclOperationDelete);

            $adminMenu = new Menu('Forms', ':Admin:Form:Form', 'fa-credit-card', $aclOperationEdit);

            $foundRoot = $adminMenuRepository->getOneByName('Site items');

            if ($foundRoot)
            {
                $adminMenuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $foundRoot);
            }
            else
            {
                $entityManager->persist($adminMenu);
            }

            $entityManager->flush();

            $output->writeLn('Module installed successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}