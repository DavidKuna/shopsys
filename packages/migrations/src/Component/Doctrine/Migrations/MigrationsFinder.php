<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\Finder\MigrationFinderInterface;
use Doctrine\DBAL\Migrations\Finder\RecursiveRegexFinder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class MigrationsFinder implements MigrationFinderInterface
{
    /**
     * @var \Doctrine\DBAL\Migrations\Finder\RecursiveRegexFinder
     */
    private $finder;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocator
     */
    private $locator;

    public function __construct(RecursiveRegexFinder $finder, MigrationsLocator $locator)
    {
        $this->finder = $finder;
        $this->locator = $locator;
    }

    /**
     * Find all the migrations in all registered bundles for the given path and namespace postfix
     *
     * eg. findMigrations('Migrations', 'Migrations') looks for migrations in directory Migrations in roots of all bundles
     *
     * @param   string $directory The directory within a bundle in which to look for migrations
     * @param   string|null $namespace The namespace within a bundle of the classes to load
     * @return  string[] An array of class names that were found with the version as keys.
     */
    public function findMigrations($directory, $namespace = null)
    {
        if ($namespace === null) {
            $namespace = $directory;
        }

        $migrations = [];

        foreach ($this->locator->getMigrationsLocations($directory, $namespace) as $location) {
            $migrations += $this->finder->findMigrations($location->getDirectory(), $location->getNamespace());
        }

        ksort($migrations, SORT_STRING);

        return $migrations;
    }
}
