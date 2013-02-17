<?php

namespace Msi\Bundle\CmfBundle\Admin;

use Msi\Bundle\CmfBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class PageBlockAdmin extends Admin
{
    public function configure()
    {
        $this->options = array(
            'search_fields' => array('a.type', 'a.name'),
            'controller' => 'MsiCmfBundle:Admin/PageBlock:',
            'form_template' => 'MsiCmfBundle:PageBlock:form.html.twig',
        );
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('name')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder->add('name');

        $typeId = $this->getObject()->getType();
        if ($typeId) {
            $this->container->get('msi_cmf.'.$typeId.'.block.handler')->buildForm($builder);
            $builder->add('pages', 'entity', array('multiple' => true, 'expanded' => true, 'class' => 'MsiCmfBundle:Page'));
        } else {
            $builder
                ->add('type', 'choice', array(
                    'choices' => array(
                        'text' => 'Text',
                        'action' => 'Action',
                        'template' => 'Template',
                    ),
                ))
            ;
        }
    }

    public function buildFilterForm($builder)
    {
        $builder
            ->add('pages', 'entity', array(
                'class' => 'MsiCmfBundle:Page',
                'label' => ' ',
                'empty_value' => '- '.$this->container->get('translator')->transchoice('entity.Page', 1).' -',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->addSelect('t')
                    ;
                },
            ))
            ->add('type', 'choice', array(
                'label' => ' ',
                'empty_value' => '- Type -',
                'choices' => array(
                    'text' => 'Text',
                    'action' => 'Action',
                    'template' => 'Template',
                ),
            ))
        ;
    }
}
