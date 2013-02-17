<?php

namespace Msi\Bundle\CmfBundle\EntityManager;

class MenuManager extends Manager
{
    public function findRootByName($name, $locale)
    {
        $qb = $this->getFindByQueryBuilder(
            array(
                't.name' => $name,
            ),
            array(
                'a.children' => 'lvl1',
                'lvl1.children' => 'lvl2',
                'lvl2.children' => 'lvl3',

                'a.translations' => 't',
                'lvl1.translations' => 'lvl1t',
                'lvl2.translations' => 'lvl2t',
                'lvl3.translations' => 'lvl3t',

                'a.groups' => 'g',
                'lvl1.groups' => 'lvl1g',
                'lvl2.groups' => 'lvl2g',
                'lvl3.groups' => 'lvl3g',
            )
        );

        // $orX = $qb->expr()->orX();

        // $orX->add($qb->expr()->eq('pt.locale', ':ptlocale'));
        // $qb->setParameter('ptlocale', $locale);

        // $orX->add($qb->expr()->isNull('c.page'));

        // $qb->andWhere($orX);

        // $qb->andWhere($qb->expr()->eq('ct.locale', ':ctlocale'));
        // $qb->setParameter('ctlocale', $locale);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
