<?php

namespace Hautelook\RepositoryServiceBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hautelook\RepositoryServiceBundle\DependencyInjection\Compiler\RepositoryServiceCompilerPass;

class HautelookRepositoryServiceBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryServiceCompilerPass());
    }
}
