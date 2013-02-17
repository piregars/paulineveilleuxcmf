<?php

namespace Msi\Bundle\CmfBundle\Admin;

use Msi\Bundle\CmfBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Msi\Bundle\CmfBundle\Form\Type\MenuRootTranslationType;

class MenuRootAdmin extends Admin
{
    public function configure()
    {
        $this->options = array(
            'controller' => 'MsiCmfBundle:Admin/MenuRoot:',
            'child_property' => 'children',
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
        $builder
            ->add('translations', 'collection', array('label' => ' ', 'type' => new MenuRootTranslationType(), 'options' => array(
                'label' => ' ',
            )))
        ;
    }
}
