<?php

namespace Msi\Bundle\CmfBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends ContainerAware
{
    public function showAction()
    {
        $slug = $this->container->get('request')->attributes->get('slug');
        $criteria = array('t.published' => true);
        $join = array('a.translations' => 't', 'a.blocks' => 'b');

        if ($slug) {
            $criteria['t.slug'] = $slug;
        } else {
            $criteria['a.home'] = true;
        }

        $qb = $this->container->get('msi_cmf_page_admin')->getObjectManager()->getFindByQueryBuilder($criteria, $join, array('b.position' => 'ASC'));

        $qb->andWhere($qb->expr()->isNull('a.route'));

        $page = $qb->getQuery()->getResult();

        if (!isset($page[0])) {
            throw new NotFoundHttpException();
        }

        return $this->container->get('templating')->renderResponse($page[0]->getTemplate(), array('page' => $page[0]));
    }
}
