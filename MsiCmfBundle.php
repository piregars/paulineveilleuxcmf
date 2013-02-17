<?php

namespace Msi\Bundle\CmfBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Msi\Bundle\CmfBundle\DependencyInjection\Compiler\FindAdminIdsPass;

class MsiCmfBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FindAdminIdsPass());
    }
}
