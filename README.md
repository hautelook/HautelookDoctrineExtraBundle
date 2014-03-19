RepositoryServiceBundle
=======================
[![Build Status](https://travis-ci.org/hautelook/HautelookDoctrineExtraBundle.png)](https://travis-ci.org/hautelook/HautelookDoctrineExtraBundle)

Symfony2 bundle that provides convenient additions to doctrine:

## Installation

After adding the `hautelook/doctrine-extra-bundle` to your composer.json file, add the bundle to the application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Hautelook\DoctrineExtraBundle\HautelookDoctrineExtraBundleBundle()
        // ...
    );
}
```

## Automatic repositories services

You will need to configure some information about your entities/repositories, as well as what you would like the repository service IDs to look like:

```yml
hautelook_doctrine_extra:
    entity:
        location: %kernel.root_dir%/../src/VendorName/FooBundle/Entity
        repository_location: %kernel.root_dir%/../src/VendorName/FooBundle/Entity/Repository
        service_prefix: vendor_foo.entity.repository
        namespace: VendorName\FooBundle\Entity
```


After configuring, you will be able to access your repositories services by:

```
vendor_foo.entity.repository.foo
```

If the Foo entity has a custom repository, it will be used.  Otherwise, it will be the default ```Doctrine\ORM\EntityManager```

## QueryBuilderHelper

This class is helpful when you need to join a lot of tables.

```php
use Hautelook\DoctrineExtraBundle\ORM\QueryBuilderHelper;

class UserRepository
{
    public function getUserWithGroupsAndOrders($id)
    {
        $qb = $this->createQueryBuilder('user');

        $qbHelper = new QueryBuilderHelper();
        $qbHelper->joinPropertyTree(
            $qb,
            [
                'orders' => [
                    'product' => [
                        'skus',
                    ],
                    'invoice',
                ],
                'groups,
            ]
        );


        return $qb->getQuery()->getSingleResult();
    }
}
```

This is even more helpful to not "hardcode" in the repository which relations you want fetched joined:


```php
use Hautelook\DoctrineExtraBundle\ORM\QueryBuilderHelper;

class UserRepository
{
    public function getUser($id, array $propertyTree = array())
    {
        $qb = $this->createQueryBuilder('user');

        $qbHelper = new QueryBuilderHelper();
        $qbHelper->joinPropertyTree($qb, $propertyTree);

        return $qb->getQuery()->getSingleResult();
    }
}
```

You can also control whether or not you want to left join, inner join, or if you want to fetch join or just join:

```php
/**
 * @param QueryBuilder $qb
 * @param array        $propertyTree
 * @param boolean      $leftJoin
 * @param boolean      $fetchJoin
 */
public function joinPropertyTree(QueryBuilder $qb, array $propertyTree, $leftJoin = true, $fetchJoin = true)
```
