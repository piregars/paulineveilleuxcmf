<?php

namespace Msi\Bundle\CmfBundle\Admin;

use Msi\Bundle\CmfBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Msi\Bundle\CmfBundle\Form\Type\MenuNodeTranslationType;

class MenuNodeAdmin extends Admin
{
    public function configure()
    {
        $this->options = array(
            'controller' => 'MsiCmfBundle:Admin/MenuNode:',
            'search_fields' => array('t.name'),
            'form_template' => 'MsiCmfBundle:MenuNode:form.html.twig',
        );
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('name', 'tree')
            ->add('', 'action', array('tree' => true))
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $qb = $this->getObjectManager()->getFindByQueryBuilder(
            array('a.menu' => $this->container->get('request')->query->get('parentId')),
            array('a.children' => 'c'),
            array('a.lvl' => 'ASC', 'a.lft' => 'ASC')
        );

        if ($this->getObject()->getId()) {
            $qb->andWhere('a.id != :match')->setParameter('match', $this->getObject()->getId());
            $i = 0;
            foreach ($this->getObject()->getChildren() as $child) {
                $qb->andWhere('a.id != :match'.$i)->setParameter('match'.$i, $child->getId());
                $i++;
            }
        }

        if ($this->getObject()->getChildren()->count()) {
            $qb->andWhere('a.lvl <= :bar')->setParameter('bar', $this->getObject()->getLvl() - 1);
        }

        $qb->andWhere('a.lvl <= :foo')->setParameter('foo', 2);

        $choices = $qb->getQuery()->execute();

        $builder
            ->add('translations', 'collection', array('label' => ' ', 'type' => new MenuNodeTranslationType(), 'options' => array(
                'label' => ' ',
            )))
            ->add('page', 'entity', array(
                'empty_value' => '',
                'class' => 'Msi\Bundle\CmfBundle\Entity\Page',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->orderBy('t.title', 'ASC')
                    ;
                },
            ))
            ->add('parent', 'entity', array(
                'class' => 'Msi\Bundle\CmfBundle\Entity\Menu',
                'choices' => $choices,
                'property' => 'toTree',
            ))
            ->add('targetBlank', 'checkbox')
            ->add('groups', 'entity', array(
                'class' => 'MsiUserBundle:Group',
                'multiple' => true,
                'expanded' => true,
            ))
        ;
    }
}
