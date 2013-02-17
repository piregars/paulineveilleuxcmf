<?php

namespace Msi\Bundle\CmfBundle\EntityManager;

class PageManager extends Manager
{
    public function findByRoute($route)
    {
        $page = $this->getFindByQueryBuilder(array('t.published' => true, 'a.route' => $route), array('a.translations' => 't', 'a.blocks' => 'b'), array('b.position' => 'ASC'))->getQuery()->getResult();

        if (!isset($page[0])) {
            $page = null;
        } else {
            $page = $page[0];
        }

        return $page;
    }
}
