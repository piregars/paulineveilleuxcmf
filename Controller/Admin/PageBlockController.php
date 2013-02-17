<?php

namespace Msi\Bundle\CmfBundle\Controller\Admin;

use Msi\Bundle\CmfBundle\Controller\AdminController;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\QueryBuilder;

class PageBlockController extends AdminController
{
    public function newAction(Request $request)
    {
        $this->check('create');

        if ($this->processForm()) {
            return new RedirectResponse($this->admin->genUrl('edit', array('id' => $this->admin->getObject()->getId())));
        }

        return $this->render('MsiCmfBundle:Admin:new.html.twig', array('form' => $this->admin->getForm()->createView()));
    }

    protected function configureIndexQueryBuilder(QueryBuilder $qb)
    {
        if (!$this->admin->getContainer()->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('a.isSuperAdmin = false');
        }
    }
}
