<?php

namespace Msi\Bundle\CmfBundle\Block\Handler;

use Msi\Bundle\CmfBundle\Block\BlockHandler;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionBlockHandler extends BlockHandler
{
    protected $kernel;
    protected $actions;

    public function __construct($slots, $actions, HttpKernelInterface $kernel)
    {
        parent::__construct($slots);

        $this->actions = $actions;
        $this->kernel = $kernel;
    }

    public function render($block)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $settings = $resolver->resolve($block->getSettings());
        $options = array();

        if (isset($settings['attributes'])) {
            $parts = explode('&', trim($settings['attributes']));
            foreach ($parts as $part) {
                $pieces = explode('=', trim($part));
                $options['attributes'][$pieces[0]] = $pieces[1];
            }
        }

        return $this->kernel->render($settings['action'], $options);
    }

    public function buildForm($builder, $fields = array())
    {
        $fields = array(
            array('action', 'choice', array('choices' => $this->actions)),
            array('attributes', 'text', array()),
        );

        parent::buildForm($builder, $fields);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'action',
        ));

        $resolver->setOptional(array(
            'attributes',
        ));

        parent::setDefaultOptions($resolver);
    }
}
