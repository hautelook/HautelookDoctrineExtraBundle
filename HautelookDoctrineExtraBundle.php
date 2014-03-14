<?php

namespace Hautelook\DoctrineExtraBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Hautelook\DoctrineExtraBundle\DependencyInjection\Compiler\RepositoryServiceCompilerPass;

class HautelookDoctrineExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryServiceCompilerPass());
    }
}
