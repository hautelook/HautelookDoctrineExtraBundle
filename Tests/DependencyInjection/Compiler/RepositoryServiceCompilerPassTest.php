<?php

namespace Hautelook\DoctrineExtraBundle\Tests\DependencyInjection\Compiler;

use Hautelook\DoctrineExtraBundle\DependencyInjection\Compiler\RepositoryServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RepositoryServiceCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $namespace = 'Hautelook\DoctrineExtraBundle\Resources\fixture\Entity';
        $container = new ContainerBuilder();
        $container->setParameter('hautelook_repository_service.entity.location', __DIR__  . '/../../../Resources/fixtures/Entity');
        $container->setParameter('hautelook_repository_service.entity.repository_location', __DIR__ . '/../../../Resources/fixtures/Entity');
        $container->setParameter('hautelook_repository_service.entity.service_prefix', 'hautelook_test.entity');
        $container->setParameter('hautelook_repository_service.entity.namespace', $namespace);

        $pass = new RepositoryServiceCompilerPass();
        $pass->process($container);

        $this->assertTrue($container->hasDefinition('hautelook_test.entity.foo'));
        $this->assertTrue($container->hasDefinition('hautelook_test.entity.bar'));
        $fooDefinition = $container->getDefinition('hautelook_test.entity.foo');
        $this->assertEquals($namespace . '\FooRepository', $fooDefinition->getClass());
        $this->assertEquals($namespace . '\Foo', $fooDefinition->getArgument(0));

        $barDefinition = $container->getDefinition('hautelook_test.entity.bar');
        $this->assertEquals('Doctrine\ORM\EntityRepository', $barDefinition->getClass());
        $this->assertEquals($namespace . '\Bar', $barDefinition->getArgument(0));
    }

    public function testProcessBadLocation()
    {
        $container = new ContainerBuilder();
        $container->setParameter('hautelook_repository_service.entity.location', __DIR__  . '/Hello');
        $container->setParameter('hautelook_repository_service.entity.repository_location', __DIR__ . '/../../../Resources/fixtures/Entity');
        $container->setParameter('hautelook_repository_service.entity.service_prefix', 'hautelook_test.entity');
        $container->setParameter('hautelook_repository_service.entity.namespace', 'Hautelook\DoctrineExtraBundle\Resources\fixture\Entity');
        $pass = new RepositoryServiceCompilerPass();
        $this->setExpectedException('\RuntimeException');
        $pass->process($container);
    }

    public function testProcessBadRepositoryLocation()
    {
        $container = new ContainerBuilder();
        $container->setParameter('hautelook_repository_service.entity.location',  __DIR__ . '/../../../Resources/fixtures/Entity');
        $container->setParameter('hautelook_repository_service.entity.repository_location', __DIR__  . '/Hello');
        $container->setParameter('hautelook_repository_service.entity.service_prefix', 'hautelook_test.entity');
        $container->setParameter('hautelook_repository_service.entity.namespace', 'Hautelook\DoctrineExtraBundle\Resources\fixture\Entity');
        $pass = new RepositoryServiceCompilerPass();
        $this->setExpectedException('\RuntimeException');
        $pass->process($container);
    }
}
