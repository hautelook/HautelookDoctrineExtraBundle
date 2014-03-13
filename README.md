RepositoryServiceBundle
=======================

A Symfony2 Bundle that will automatically create services for all of your entity repositories.

## Installation

After adding the `hautelook/repository-service-bundle` to your composer.json file, add the bundle to the application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Hautelook\RepositoryServiceBundle\HautelookRepositoryServiceBundle()
        // ...
    );
}
```

## Configuration

You will need to configure some information about your entities/repositories, as well as what you would like the repository service IDs to look like:

```yml
hautelook_repository_service:
    entity:
        location: %kernel.root_dir%/../src/VendorName/FooBundle/Entity
        repository_location: %kernel.root_dir%/../src/VendorName/FooBundle/Entity/Repository
        service_prefix: vendor_foo.entity.repository
        namespace: VendorName\FooBundle\Entity

```

## Usage

After configuring, you will be able to access your repositories services by:

```
vendor_foo.entity.repository.foo
```

If the Foo entity has a custom repository, it will be used.  Otherwise, it will be the default ```Doctrine\ORM\EntityManager```
