<?php

namespace Hautelook\DoctrineExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Create service ids for all the entity repository classes
 * @author Brandon Woodmansee <brandon.woodmansee@hautelook.com>
 */
class RepositoryServiceCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $entityLocation = $container->getParameter('hautelook_doctrine_extra.entity.location');
        $entityRepositoryLocation = $container->getParameter('hautelook_doctrine_extra.entity.repository_location');
        $repositoryClasses = $this->getRepositoryClasses($entityRepositoryLocation);
        foreach ($repositoryClasses as $repository) {
            $this->createCustomRepositoryDefinition($container, $repository);
        }

        $entityClasses = $this->getEntityClasses($entityLocation);
        foreach ($entityClasses as $entity) {
            $this->createDefaultRepositoryDefinition($container, $entity);
        }
    }

    /**
     * @param  ContainerBuilder $container
     * @param  string           $className
     * @return null
     */
    private function createCustomRepositoryDefinition(ContainerBuilder $container, $className)
    {
        $entityName = $this->getEntityNameFromRepositoryClass($className);
        if (null === $entityName) {
            return null;
        }

        $classNamespace = sprintf(
            '%s\%s',
            $this->getEntityRepositoryNamespace(
                $container->getParameter('hautelook_doctrine_extra.entity.location'),
                $container->getParameter('hautelook_doctrine_extra.entity.repository_location'),
                $container->getParameter('hautelook_doctrine_extra.entity.namespace')
            ),
            $className
        );

        $this->createDefinition($container, $classNamespace, $entityName);
    }

    /**
     * Derive the entity repository namespace
     * @param $entityLocation
     * @param $repositoryLocation
     * @param $entityNamespace
     * @return string
     */
    private function getEntityRepositoryNamespace($entityLocation, $repositoryLocation, $entityNamespace)
    {
        if ($entityLocation === $repositoryLocation) {
            return $entityNamespace;
        }
        $additionalNamespaceElement = str_replace($entityLocation, null, $repositoryLocation);

        return $entityNamespace . str_replace('/', '\\', $additionalNamespaceElement);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $entityName
     */
    private function createDefaultRepositoryDefinition(ContainerBuilder $container, $entityName)
    {
        $this->createDefinition($container, 'Doctrine\ORM\EntityRepository', $entityName);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $class
     * @param string           $entityName
     */
    private function createDefinition(ContainerBuilder $container, $class, $entityName)
    {
        $serviceId = $this->getServiceId(
            $container->getParameter('hautelook_doctrine_extra.entity.service_prefix'),
            $entityName
        );
        // Definition exists for this service id, do nothing
        if ($container->hasDefinition($serviceId)) {
            return;
        }
        /** @var Definition $definition */
        $definition = $container->register($serviceId);
        $definition->setFactoryService('doctrine');
        $definition->setClass($class);
        $definition->setFactoryMethod('getRepository');
        $entityNamespace = $container->getParameter('hautelook_doctrine_extra.entity.namespace');
        $definition->addArgument(sprintf('%s\%s', $entityNamespace, $entityName));
    }

    /**
     * @param  string $serviceIdPrefix
     * @param  string $entityName
     * @return string
     */
    private function getServiceId($serviceIdPrefix, $entityName)
    {
        return sprintf(
            '%s.%s',
            $serviceIdPrefix,
            // Turn CamelCase into camel_case
            Inflector::tableize($entityName)
        );
    }

    /**
     * @param $className
     * @return mixed
     */
    private function getEntityNameFromRepositoryClass($className)
    {
        preg_match('/([a-zA-Z]+)Repository/', $className, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
    }

    /**
     * Find all the repository classes
     * @param  string            $entityRepositoryLocation
     * @return array
     * @throws \RuntimeException
     */
    private function getRepositoryClasses($entityRepositoryLocation)
    {
        if (!$this->pathExists($entityRepositoryLocation)) {
            throw new \RuntimeException('Invalid repository path.');
        }
        $finder = new Finder();
        $finder->files()->name('*Repository.php')->in($entityRepositoryLocation)->depth(0);

        return $this->findClasses($finder);
    }

    /**
     * @param  string            $entityLocation
     * @return array
     * @throws \RuntimeException
     */
    private function getEntityClasses($entityLocation)
    {
        if (!$this->pathExists($entityLocation)) {
            throw new \RuntimeException('Invalid entity location.');
        }
        $finder = new Finder();
        $finder->files()->name('*.php')->notName('*Repository.php')->in($entityLocation)->depth(0);

        return $this->findClasses($finder);
    }

    /**
     * @param  Finder $finder
     * @return array
     */
    private function findClasses(Finder $finder)
    {
        $classes = array();
        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $classes[] = str_replace('.php', null, $file->getRelativePathName());
        }

        return $classes;
    }

    /**
     * @param  string $path
     * @return bool
     */
    private function pathExists($path)
    {
        $fs = new Filesystem();

        return $fs->exists($path);
    }
}
