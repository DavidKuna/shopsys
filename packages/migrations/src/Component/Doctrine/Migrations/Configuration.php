<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration as DoctrineConfiguration;
use Doctrine\DBAL\Migrations\Finder\MigrationFinderInterface;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\QueryWriter;
use Doctrine\DBAL\Migrations\Version;

class Configuration extends DoctrineConfiguration
{
    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsConfig
     */
    private $migrationsConfig;

    /**
     * @var \Doctrine\DBAL\Migrations\Version[]
     */
    private $migrations = [];

    /**
     * @inheritdoc
     */
    public function __construct(
        MigrationsConfig $migrationsConfig,
        Connection $connection,
        OutputWriter $outputWriter = null,
        MigrationFinderInterface $finder = null,
        QueryWriter $queryWriter = null
    ) {
        $this->migrationsConfig = $migrationsConfig;

        parent::__construct($connection, $outputWriter, $finder, $queryWriter);
    }

    /**
     * @inheritdoc
     */
    public function getMigrations()
    {
        if (count($this->migrations) === 0) {
            $migrations = parent::getMigrations();
            $orderedMigrations = [];

            $orderedMigrationsToInstall = $this->migrationsConfig->getOrderedMigrationsToInstall();

            foreach (array_keys($orderedMigrationsToInstall) as $version) {
                if (array_key_exists($version, $migrations)) {
                    $orderedMigrations[$version] = $migrations[$version];
                }
            }

            $this->migrations = $orderedMigrations;
        }

        return $this->migrations;
    }

    /**
     * @inheritdoc
     */
    public function getMigrationsToExecute($direction, $to)
    {
        if ($direction === Version::DIRECTION_DOWN) {
            if (count($this->getMigrations())) {
                $allVersions = array_reverse(array_keys($this->migrations));
                $classes     = array_reverse(array_values($this->migrations));
                $allVersions = array_combine($allVersions, $classes);
            } else {
                $allVersions = [];
            }
        } else {
            $allVersions = $this->getMigrations();
        }

        $versions = [];
        $migrated = $this->getMigratedVersions();

        foreach ($allVersions as $version) {
            if ($this->shouldExecuteMigration($direction, $version, $to, $migrated)) {
                $versions[$version->getVersion()] = $version;
            }
        }

        return $versions;
    }

    /**
     * @inheritdoc
     */
    private function shouldExecuteMigration($direction, Version $version, $to, $migrated)
    {
        $isVersionInstalled = in_array($version->getVersion(), $migrated, true);

        if ($direction === Version::DIRECTION_DOWN) {
            return $isVersionInstalled;
        } else {
            return !$isVersionInstalled;
        }
    }
}
