<?php

namespace Msi\Bundle\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'tree' => false,
            'soft_delete' => false,
            'actions' => array(),
            'attr' => array('class' => 'span1'),
        ));
    }
}
