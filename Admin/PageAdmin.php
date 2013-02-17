<?php

namespace Msi\Bundle\CmfBundle\Admin;

use Msi\Bundle\CmfBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\CmfBundle\Form\Type\PageTranslationType;

class PageAdmin extends Admin
{
    public function configure()
    {
        $this->options = array(
            'form_template' => 'MsiCmfBundle:Page:form.html.twig',
        );
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('title')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $collection = $this->container->get('router')->getRouteCollection();
        $choices = array();
        foreach ($collection->all() as $name => $route) {
            if (preg_match('#^_#', $name)) {
                continue;
            }
            if (preg_match('#^msi_page_#', $name)) {
                continue;
            }
            $choices[$name] = $name;
        }

        $builder
            ->add('template', 'choice', array('choices' => $this->container->getParameter('msi_cmf.page.layouts')))
            ->add('home')
            ->add('route', 'choice', array(
                'empty_value' => '',
                'choices' => $choices,
            ))
            ->add('css', 'textarea')
            ->add('js', 'textarea')
            ->add('translations', 'collection', array('label' => ' ', 'type' => new PageTranslationType(), 'options' => array(
                'label' => ' ',
            )))
        ;
    }

    public function buildFilterForm($builder)
    {
        $builder->add('home', 'checkbox');
    }
}
