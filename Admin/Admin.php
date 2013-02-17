<?php

/*
 * This file is part of the MsiCmfBundle package.
 *
 * (c) Alexis Joubert <alexisjoubert@groupemsi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Msi\Bundle\CmfBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Msi\Bundle\CmfBundle\EntityManager\Manager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\CmfBundle\Grid\GridBuilder;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class Admin
{
    protected $options = array();

    protected $id;
    protected $child;
    protected $parent;
    protected $entity;
    protected $parentEntity;
    protected $container;
    protected $objectManager;
    protected $forms;
    protected $grids;

    public function __construct(Manager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    abstract public function buildGrid(GridBuilder $builder);

    abstract public function buildForm(FormBuilder $builder);

    public function configure()
    {
    }

    public function prePersist($entity)
    {
    }

    public function postPersist($entity)
    {
    }

    public function preUpdate($entity)
    {
    }

    public function postUpdate($entity)
    {
    }

    public function postLoad(ArrayCollection $collection)
    {
    }

    public function getLabel($number = 1, $locale = null)
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $class = substr($class, 0, -5);

        return $this->container->get('translator')->transChoice('entity.'.$class, $number, array(), 'messages', $locale);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        $this->configure();
        $this->init();

        return $this;
    }

    public function getBundleName()
    {
        $parts = explode('_', $this->id);

        return ucfirst($parts[0]).ucfirst($parts[1]).'Bundle';
    }

    public function getAction()
    {
        return preg_replace(array('#^[a-z]+_([a-z]+_){1,2}[a-z]+_[a-z]+_#'), array(''), $this->container->get('request')->attributes->get('_route'));
    }

    public function isSortable()
    {
        return property_exists($this->getObjectManager()->getClass(), 'position');
    }

    public function isTranslatable()
    {
        return is_subclass_of($this->getObjectManager()->getClass(), 'Msi\Bundle\CmfBundle\Entity\Translatable');
    }

    public function isTranslationField($field)
    {
        if ($this->isTranslatable()) {
            return property_exists($this->getObject()->getTranslation(), $field);
        }
    }

    public function getClass()
    {
        return $this->getObjectManager()->getClass();
    }

    public function getClassName()
    {
        return substr($this->getObjectManager()->getClass(), strrpos($this->getObjectManager()->getClass(), '\\') + 1);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getObject()
    {
        if (!$this->object) {
            $this->object = $this->objectManager->findOneOrCreate($this->container->get('request')->attributes->get('id'));
        }

        return $this->object;
    }

    public function getParentObject()
    {
        if (!$this->parentObject) {
            $this->parentObject = $this->getParent()->objectManager->findOneOrCreate($this->container->get('request')->query->get('parentId'));
        }

        return $this->parentObject;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getAppLocales()
    {
        return $this->container->getParameter('msi_cmf.app_locales');
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    public function getChild()
    {
        return $this->child;
    }

    public function setChild(Admin $child)
    {
        $this->child = $child;
        if (!$child->hasParent()) $child->setParent($this);
    }

    public function hasChild()
    {
        return $this->child instanceof Admin;
    }

    public function countChildElements($object)
    {
        if (!$this->hasChild()) {
            throw new \Exception('Cannot call countChildElements. Admin doesn\'t have a child.');
        }

        $getter = 'get'.ucfirst($this->getOption('child_property'));
        $count = 0;

        if (property_exists($this->getChild()->getClass(), $this->getOption('child_property'))) {
            $this->countChildElementsRecursively($object, $getter, $count);
        } else {
            $count = $object->$getter()->count();
        }

        return $count;
    }

    public function countChildElementsRecursively($object, $getter, &$count)
    {
        foreach ($object->$getter() as $child) {
            if ($child->$getter()->count()) {
                $this->countChildElementsRecursively($child, $getter, $count);
            }
            $count++;
        }
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;
        if (!$parent->hasChild()) $parent->setChild($this);
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
    }

    public function createGridBuilder()
    {
        return new GridBuilder();
    }

    public function getGrid($name = '')
    {
        if (!isset($this->grids[$name])) {
            $method = 'build'.ucfirst($name).'Grid';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createGridBuilder();
            $this->$method($builder);
            $this->grids[$name] = $builder->getGrid();
        }

        return $this->grids[$name];
    }

    public function createFormBuilder($name, $data = null, array $options = array())
    {
        if (!$name) $name = $this->id;

        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }

    public function getForm($name = '')
    {
        if (!isset($this->forms[$name])) {
            $method = 'build'.ucfirst($name).'Form';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createFormBuilder($name, $name ? null : $this->getObject(), array('cascade_validation' => true));
            $this->$method($builder);
            $this->forms[$name] = $builder->getForm();
        }

        return $this->forms[$name];
    }

    public function isGranted($role)
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->id.'_'.$role))) {
            return false;
        } else {
            return true;
        }
    }

    public function genUrl($route, $parameters = array(), $mergePersistentParameters = true, $absolute = false)
    {
        if (true === $mergePersistentParameters) {
            $query = $this->container->get('request')->query;
            $persistant = array(
                'page' => $query->get('page'),
                'q' => $query->get('q'),
                'parentId' => $query->get('parentId'),
                'filter' => $query->get('filter'),
            );
            $parameters = array_merge($persistant, $parameters);
        }

        return $this->container->get('router')->generate($this->id.'_'.$route, $parameters, $absolute);
    }

    public function buildBreadcrumb()
    {
        $request = $this->container->get('request');
        $action = $this->getAction();
        $crumbs = array();

        if ($this->hasParent()) {
            $crumbs[] = array('label' => $this->getParent()->getLabel(2), 'path' => $this->getParent()->genUrl('index'));
            $crumbs[] = array('label' => $this->getParentObject(), 'path' => $this->getParent()->genUrl('edit', array('id' => $this->getParentObject()->getId())));
        }

        $crumbs[] = array('label' => $this->getLabel(2), 'path' => 'index' !== $action ? $this->genUrl('index') : '', 'class' => 'index' !== $action ? '' : 'active');

        if ($action === 'new') {
            $crumbs[] = array('label' => $this->container->get('translator')->trans('Add'), 'path' => '', 'class' => 'active');
        }

        if ($action === 'edit') {
            $crumbs[] = array('label' => $this->container->get('translator')->trans('Edit'), 'path' => '', 'class' => 'active');
        }

        if ($action === 'show') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => '', 'class' => 'active');
        }

        return $crumbs;
    }

    protected function init()
    {
        $this->object = null;
        $this->parentObject = null;
        $this->forms = array();
        $this->tables = array();

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'controller'        => 'MsiCmfBundle:Admin:',
            'form_template'     => 'MsiCmfBundle:Admin:form.html.twig',
            'index_template'    => 'MsiCmfBundle:Admin:index.html.twig',
            'new_template'      => 'MsiCmfBundle:Admin:new.html.twig',
            'edit_template'     => 'MsiCmfBundle:Admin:edit.html.twig',
            'search_fields' => array('a.id'),
        ));

        if ($this->hasChild()) {
            $resolver->setRequired(array('child_property'));
        }
    }
}
