<?php

namespace Msi\Bundle\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
}
