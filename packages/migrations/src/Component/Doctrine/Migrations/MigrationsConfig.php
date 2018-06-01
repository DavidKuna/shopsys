<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class MigrationsConfig
{
    const MIGRATIONS_CONFIG_FILE_NAME = 'migrations_config.yml';

    /**
     * @var string
     */
    private $migrationsConfigPath;

    public function __construct(ContainerInterface $container)
    {
        $this->migrationsConfigPath = $container->getParameter('shopsys.root_dir') . '/' . self::MIGRATIONS_CONFIG_FILE_NAME;
    }

    /**
     * @return string[]
     */
    private function getMigrationsConfig()
    {
        if (file_exists($this->migrationsConfigPath)) {
            return Yaml::parseFile($this->migrationsConfigPath);
        }

        return [];
    }

    /**
     * @return string[]
     */
    public function getOrderedMigrationsToInstall()
    {
        $migrationsConfig = $this->getMigrationsConfig();

        $migrationsToInstall = [];

        foreach ($migrationsConfig as $migrationVersion => $migrationConfig) {

            if (!$migrationConfig['skip']) {
                $migrationsToInstall[$migrationVersion] = $migrationConfig['file'];
            }
        }

        return $migrationsToInstall;
    }

    /**
     * @param string[] $migrations
     */
    public function updateMigrationsConfig(array $migrations)
    {
        $migrationsConfig = $this->getMigrationsConfig();

        foreach ($migrations as $migrationVersion => $migrationFile) {
            if (!array_key_exists($migrationVersion, $migrationsConfig)) {
                $migrationsConfig[$migrationVersion] = [
                    'file' => $migrationFile,
                    'skip' => 0
                ];
            }
        }

        $this->saveMigrationsConfig($migrationsConfig);
    }

    /**
     * @param string[] $migrationsConfig
     */
    private function saveMigrationsConfig(array $migrationsConfig)
    {
        $yamlMigrationsConfig = Yaml::dump($migrationsConfig);

        file_put_contents($this->migrationsConfigPath, $yamlMigrationsConfig);
    }
}