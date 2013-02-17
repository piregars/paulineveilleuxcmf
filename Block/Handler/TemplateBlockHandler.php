<?php

namespace Msi\Bundle\CmfBundle\Block\Handler;

use Msi\Bundle\CmfBundle\Block\BlockHandler;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TemplateBlockHandler extends BlockHandler
{
    protected $templates;

    public function __construct($slots, $templates)
    {
        parent::__construct($slots);

        $this->templates = $templates;
    }

    public function render($block)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $settings = $resolver->resolve($block->getSettings());

        return $this->templating->render($settings['template'], array('settings' => $settings));
    }

    public function buildForm($builder, $fields = array())
    {
        $fields = array(
            array('template', 'choice', array('choices' => $this->templates)),
        );

        parent::buildForm($builder, $fields);
    }
}
