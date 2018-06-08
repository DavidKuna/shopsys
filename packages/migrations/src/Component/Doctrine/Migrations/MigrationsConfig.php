<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class MigrationsConfig
{
    /**
     * @var string
     */
    private $migrationsConfigPath;

    public function __construct($migrationsConfigPath)
    {
        $this->migrationsConfigPath = $migrationsConfigPath;
    }

    /**
     * @return string[]
     */
    private function getMigrationsSetting()
    {
        if (file_exists($this->migrationsConfigPath)) {
            return Yaml::parseFile($this->migrationsConfigPath);
        }

        return [];
    }

    /**
     * @param \Doctrine\DBAL\Migrations\Version[] $migrations
     * @return string[]
     */
    public function getOrderedMigrationsToInstall(array $migrations)
    {
        $allMigrationsSettings = $this->getAllMigrationsSettings($migrations);

        $migrationsToInstall = [];

        foreach ($allMigrationsSettings as $migrationVersion => $migrationSetting) {

            if (!$migrationSetting['skip']) {
                $migrationsToInstall[$migrationVersion] = $migrationSetting['namespace'];
            }
        }

        return $migrationsToInstall;
    }

    /**
     * @param \Doctrine\DBAL\Migrations\Version[] $migrations
     */
    public function updateMigrationsConfig(array $migrations)
    {
        $allMigrationsSettings = $this->getAllMigrationsSettings($migrations);

        $this->saveMigrationsConfig($allMigrationsSettings);
    }

    /**
     * @param string[] $migrationsConfig
     */
    private function saveMigrationsConfig(array $migrationsConfig)
    {
        $yamlMigrationsConfig = Yaml::dump($migrationsConfig);

        file_put_contents($this->migrationsConfigPath, $yamlMigrationsConfig);
    }

    /**
     * @param \Doctrine\DBAL\Migrations\Version[] $migrations
     * @return string[]
     */
    private function getAllMigrationsSettings(array $migrations)
    {
        $migrationsConfig = $this->getMigrationsSetting();

        foreach ($migrations as $migrationVersion => $version) {
            if (!array_key_exists($migrationVersion, $migrationsConfig)) {
                $migrationsConfig[$migrationVersion] = [
                    'namespace' => get_class($version->getMigration()),
                    'skip' => false
                ];
            }
        }

        return $migrationsConfig;
    }
}
