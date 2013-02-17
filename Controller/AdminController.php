<?php

namespace Msi\Bundle\CmfBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminController extends ContainerAware
{
    protected $admin;

    public function indexAction(Request $request)
    {
        $this->check('read');

        $qb = $this->getIndexQueryBuilder($request, $this->admin);

        // Filters
        $parameters = array();
        $filterForm = $this->admin->getForm('filter');

        if ($filterForm) {
            $this->getFilterFormHandler()->process($filterForm, $this->admin->getObject(), $qb);
            $parameters['filterForm'] = $filterForm->createView();
        }

        // Pager
        $pager = $this->container->get('msi_cmf.pager.factory')->create($qb, array('attr' => array('class' => 'pull-right')));
        $pager->paginate($request->query->get('page', 1), $this->container->get('session')->get('limit', 10));

        // Table
        $grid = $this->admin->getGrid();
        if (property_exists($this->admin->getObjectManager()->getClass(), 'position')) {
            $grid->setSortable(true);
        }

        $result = new ArrayCollection($pager->getIterator()->getArrayCopy());
        $this->admin->postLoad($result);
        $grid->setRows($result);

        $parameters['pager'] = $pager;

        return $this->render($this->admin->getOption('index_template'), $parameters);
    }

    public function newAction(Request $request)
    {
        $this->check('create');

        if ($this->processForm()) {
            if (!$request->query->has('alt')) {
                return $this->onSuccess();
            } else {
                $this->container->get('session')->getFlashBag()->add('success', $this->container->get('translator')->trans('The action was executed successfully!'));
                return new RedirectResponse($this->admin->genUrl('new'));
            }
        }

        return $this->render($this->admin->getOption('new_template'), array('form' => $this->admin->getForm()->createView()));
    }

    public function editAction(Request $request)
    {
        $request->getMethod() === 'GET' ? $this->check('read') : $this->check('update');

        if ($this->processForm()) {
            if (!$request->query->has('alt')) {
                return $this->onSuccess();
            } else {
                $this->container->get('session')->getFlashBag()->add('success', $this->container->get('translator')->trans('The action was executed successfully!'));
            }
        }

        return $this->render($this->admin->getOption('edit_template'), array('form' => $this->admin->getForm()->createView(), 'id' => $this->admin->getObject()->getId()));
    }

    public function deleteAction(Request $request)
    {
        $this->check('delete');

        $this->admin->getObjectManager()->delete($this->admin->getObject());

        return $this->onSuccess();
    }

    public function softDeleteAction(Request $request)
    {
        $this->check('update');

        $this->admin->getObjectManager()->save($this->admin->getObject()->setDeletedAt(new \DateTime()));

        return $this->onSuccess();
    }

    public function removeFileAction(Request $request)
    {
        $this->check('update');

        if ($this->admin->isTranslationField('filename')) {
            $entity = $this->admin->getObject()->getTranslation($request->query->get('locale'));
            $file = $entity->getPath().'/'.$entity->getFilename();
            $entity->setFilename(null);
            $this->admin->getObjectManager()->save($entity);
        } else {
            $entity = $this->admin->getObject();
            $file = $entity->getPath().'/'.$entity->getFilename();
            $entity->setFilename(null);
            $this->admin->getObjectManager()->save($entity);
        }

        if (is_file($file)) unlink($file);

        return $this->onSuccess();
    }

    public function changeAction(Request $request)
    {
        $this->check('update');

        $this->admin->getObjectManager()->change($this->admin->getObject(), $request);

        return $this->onSuccess();
    }

    public function sortAction(Request $request)
    {
        $this->check('update');

        $current = $request->query->get('current');
        $next = $request->query->get('next');
        $prev = $request->query->get('prev');

        $criteria = array();
        if ($request->query->get('parentId')) {
            $criteria['a.'.lcfirst($this->admin->getParent()->getClassName())] = $request->query->get('parentId');
        }

        $objects = $this->admin->getObjectManager()->getFindByQueryBuilder($criteria, array(), array('a.position' => 'ASC'))->getQuery()->execute();
        $currentObject = $this->admin->getObjectManager()->getFindByQueryBuilder(array('a.id' => $current))->getQuery()->getSingleResult();

        // reste a implementer le prev..

        $i = 1;
        foreach ($objects as $object) {
            if ($object->getId() == $current) continue;

            if ($object->getId() == $next) {
                $currentObject->setPosition($i);
                $this->admin->getObjectManager()->save($currentObject);
                $i++;
            }

            $object->setPosition($i);
            $this->admin->getObjectManager()->save($object);
            $i++;
        }

        return new Response();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->admin = $this->container->get($this->container->get('request')->attributes->get('_admin'));
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['admin'] = $this->admin;

        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }

    // override this method if you need a custom form handler for your filters
    protected function getFilterFormHandler()
    {
        return $this->container->get('msi_cmf.filter.form.handler');
    }

    // override this method if you need a custom form handler for your admin
    protected function getAdminFormHandler()
    {
        return $this->container->get('msi_cmf.admin.form.handler');
    }

    protected function getIndexQueryBuilder()
    {
        $where = array();
        $join = array();
        $sort = array();

        // sortable
        if (property_exists($this->admin->getObject(), 'position')) {
            $sort['a.position'] = 'ASC';
        }

        // translations
        if (property_exists($this->admin->getObject(), 'translations')) {
            $join['a.translations'] = 't';
        }

        // nested set
        if ($this->admin->hasParent() && $this->container->get('request')->query->get('parentId')) {
            $where['a.'.strtolower($this->admin->getParent()->getClassName())] = $this->container->get('request')->query->get('parentId');
        }

        if (!$this->container->get('request')->query->get('q')) {
            $qb = $this->admin->getObjectManager()->getFindByQueryBuilder($where, $join, $sort);
        } else {
            $qb = $this->admin->getObjectManager()->getSearchQueryBuilder($this->container->get('request')->query->get('q'), $this->admin->getOption('search_fields'), $where, $join, $sort);
        }

        // soft delete
        if (property_exists($this->admin->getObject(), 'deletedAt')) {
            $qb->andWhere($qb->expr()->isNull('a.deletedAt'));
        }

        $this->configureIndexQueryBuilder($qb);

        return $qb;
    }

    protected function configureIndexQueryBuilder(QueryBuilder $qb)
    {
    }

    protected function processForm()
    {
        $form = $this->admin->getForm();
        $process = $this->getAdminFormHandler()->setAdmin($this->admin)->process($form);

        return $process;
    }

    protected function onSuccess()
    {
        if ($this->container->get('request')->isXmlHttpRequest()) {
            return new Response('ok');
        } else {
            $this->container->get('session')->getFlashBag()->add('success', $this->container->get('translator')->trans('The action was executed successfully!'));
            return new RedirectResponse($this->admin->genUrl('index'));
        }
    }

    protected function check($role)
    {
        if (!$this->admin->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }
}
