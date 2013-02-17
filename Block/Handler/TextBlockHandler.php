<?php

namespace Msi\Bundle\CmfBundle\Block\Handler;

use Msi\Bundle\CmfBundle\Block\BlockHandler;
use Msi\Bundle\CmfBundle\Form\Type\PageBlockTranslationType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextBlockHandler extends BlockHandler
{
    public function render($block)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $settings = $resolver->resolve($block->getSettings());

        return $block->getTranslation()->getSetting('body');
    }

    public function buildForm($builder, $fields = array())
    {
        parent::buildForm($builder, $fields);

        $builder->add('translations', 'collection', array('label' => ' ', 'type' => new PageBlockTranslationType(), 'options' => array(
                'label' => ' ',
            )));
    }
}
