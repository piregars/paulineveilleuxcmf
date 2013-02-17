<?php

namespace Msi\Bundle\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NumberColumn extends BaseColumn
{
    public function fixValue()
    {
        $this->value = number_format(intval($this->value), $this->options['decimals']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'decimals' => 0,
        ));
    }
}
