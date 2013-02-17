<?php

namespace Msi\Bundle\CmfBundle\Twig\Extension;

class CmfExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'msi_is_image' => new \Twig_Function_Method($this, 'isImage', array('is_safe' => array('html'))),
        );
    }

    public function getGlobals()
    {
        $globals = array();

        $globals['app_locales'] = $this->container->getParameter('msi_cmf.app_locales');

        if (!$this->container->isScopeActive('request')) {
            return $globals;
        }

        $request = $this->container->get('request');

        $page = $this->container->get('msi_cmf.page_manager')->findByRoute($request->attributes->get('_route'));
        if (!$page) {
            $page = $this->container->get('msi_cmf.page_manager')->findOneOrCreate();
        }

        $globals['page'] = $page;

        return $globals;
    }

    public function isImage($pathname)
    {
        if (!is_file($_SERVER['DOCUMENT_ROOT'].$pathname)) {
            return false;
        }

        $handle = @getimagesize($_SERVER['DOCUMENT_ROOT'].$pathname);

        return $handle ? true : false;
    }

    public function getName()
    {
        return 'msi_cmf';
    }
}
