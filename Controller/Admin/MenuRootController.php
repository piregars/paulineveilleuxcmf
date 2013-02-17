<?php

namespace Msi\Bundle\CmfBundle\Controller\Admin;

use Msi\Bundle\CmfBundle\Controller\AdminController;
use Doctrine\ORM\QueryBuilder;

class MenuRootController extends AdminController
{
    public function configureIndexQueryBuilder(QueryBuilder $qb)
    {
        $qb->andWhere('a.lvl = :lvl')->setParameter('lvl', 0);
    }
}
