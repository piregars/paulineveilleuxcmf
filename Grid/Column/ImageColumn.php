<?php

namespace Msi\Bundle\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'image',
        ));
    }
}
