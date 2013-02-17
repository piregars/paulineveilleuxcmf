<?php

namespace Msi\Bundle\CmfBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FindAdminIdsPass implements CompilerPassInterface
{
    function process(ContainerBuilder $container)
    {
        $ids = array();
        foreach ($container->findTaggedServiceIds('msi.admin') as $id => $tags) {
            $ids[] = $id;
            $admin = $container->getDefinition($id);
            $admin->addMethodCall('setId', array($id));
        }

        $container->setParameter('msi_cmf.admin_ids', $ids);
    }
}
