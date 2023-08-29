<?php

namespace scripts;

use timanthonyalexander\BaseApi\model\Mail\MailModel;
use timanthonyalexander\BaseApi\model\Migration\MigrationModel;
use timanthonyalexander\BaseApi\module\DependencyInjection\DIContainer;
use timanthonyalexander\BaseApi\module\QueryBuilder\QueryBuilder;
use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/MigrationGenerator.php';

function Run(): bool
{
    $dicontainer = new DIContainer();
    $systemConfig = $dicontainer->get(SystemConfig::class);
    assert($systemConfig instanceof SystemConfig);

    $all = false;
    $allModels = MigrationGenerator::getAllModels();

    foreach ($allModels as $model) {
        $migrationGenerator = new MigrationGenerator($model);
        $migrationGenerator->createMigrations();

        QueryBuilder::create()->useDefaultDB();

        foreach ($migrationGenerator->do as $do) {
            $all = false;

            assert($do instanceof MigrationModel);
            print $do->do . PHP_EOL;
            $all = true;
        }
    }

    return $all;
}

$run = Run();

if ($run) {
    print "Migrations displayed. Rerun." . PHP_EOL;
}

print "Done!" . PHP_EOL;
