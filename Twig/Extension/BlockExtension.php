<?php

namespace Msi\Bundle\CmfBundle\Twig\Extension;

class BlockExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'msi_block_render' => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
        );
    }

    public function renderBlock($slot, $parent)
    {
        $content = '';

        foreach ($parent->getBlocks() as $block) {
            if ($block->getSetting('slot') === $slot && $block->getTranslation()->getPublished()) {
                $handler = $this->container->get('msi_cmf.'.$block->getType().'.block.handler');

                $content .= $handler->render($block);
            }
        }

        return $content;
    }

    public function getName()
    {
        return 'msi_block';
    }
}
