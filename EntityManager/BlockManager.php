<?php

namespace Msi\Bundle\CmfBundle\EntityManager;

use Doctrine\ORM\QueryBuilder;
use Msi\Bundle\CmfBundle\Admin\Admin;

class BlockManager extends Manager
{
    protected function configureAdminListQuery(QueryBuilder $qb, Admin $admin)
    {
        if (!$admin->getContainer()->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('a.isSuperAdmin = false');
        }
    }
}
