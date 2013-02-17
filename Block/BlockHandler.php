<?php

namespace Msi\Bundle\CmfBundle\Block;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BlockHandler
{
    protected $slots;

    public function __construct($slots)
    {
        $this->slots = $slots;
    }

    public function buildForm($builder, $fields = array())
    {
        $builder->add('settings', 'msi_block_settings', array(
            'fields' => array_merge(array(array('slot', 'choice', array('label' => 'Position', 'choices' => $this->slots))), $fields),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'slot',
        ));
    }
}
