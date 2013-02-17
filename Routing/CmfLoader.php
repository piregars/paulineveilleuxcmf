<?php

namespace Msi\Bundle\CmfBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class CmfLoader implements LoaderInterface
{
    private $loaded = false;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $collection = new RouteCollection();

        foreach ($this->container->getParameter('msi_cmf.admin_ids') as $id) {
            $admin = $this->container->get($id);
            $collection->addCollection($this->buildRoutes($admin));
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'msi_cmf' === $type;
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    protected function buildRoutes($admin)
    {
        $collection = new RouteCollection();
        $namespace = str_replace(' ', '-', strtolower($admin->getLabel(2, 'en')));
        $prefix = '/{_locale}/admin/'.$namespace;
        $suffix = '';

        $names = array(
            'index',
            'new',
            'edit',
            'delete',
            'change',
            'sort',
            'removeFile',
        );

        foreach ($names as $name) {
            $collection->add(
                $admin->getId().'_'.$name,
                new Route(
                    $prefix.'/'.$name.$suffix,
                    array(
                        '_controller' => $admin->getOption('controller').$name,
                        '_admin' => $admin->getId(),
                    )
                )
            );
        }

        $collection->add(
            $admin->getId().'_index',
            new Route(
                $prefix.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'index',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'GET',
                )
            )
        );

        $collection->add(
            $admin->getId().'_new',
            new Route(
                $prefix.'/new'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'new',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'GET|POST',
                )
            )
        );

        $collection->add(
            $admin->getId().'_edit',
            new Route(
                $prefix.'/{id}/edit'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'edit',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'GET|PUT',
                )
            )
        );

        $collection->add(
            $admin->getId().'_delete',
            new Route(
                $prefix.'/{id}/delete'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'delete',
                    '_admin' => $admin->getId(),
                ),
                array(
                    '_method' => 'DELETE',
                )
            )
        );

        $collection->add(
            $admin->getId().'_softDelete',
            new Route(
                $prefix.'/{id}/soft-delete'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'softDelete',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_change',
            new Route(
                $prefix.'/{id}/toggle-boolean-property'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'change',
                    '_admin' => $admin->getId(),
                )
            )
        );

        $collection->add(
            $admin->getId().'_removeFile',
            new Route(
                $prefix.'/{id}/remove-file'.$suffix,
                array(
                    '_controller' => $admin->getOption('controller').'removeFile',
                    '_admin' => $admin->getId(),
                )
            )
        );

        return $collection;
    }
}
