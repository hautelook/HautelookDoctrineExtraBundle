<?php

namespace Hautelook\DoctrineExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HautelookDoctrineExtraExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['entity'])) {
            $container->setParameter(
                'hautelook_repository_service.entity.location',
                $config['entity']['location']
            );
            $container->setParameter(
                'hautelook_repository_service.entity.repository_location',
                $config['entity']['repository_location']
            );
            $container->setParameter(
                'hautelook_repository_service.entity.service_prefix',
                $config['entity']['service_prefix']
            );
            $container->setParameter(
                'hautelook_repository_service.entity.namespace',
                $config['entity']['namespace']
            );
        }
    }
}
