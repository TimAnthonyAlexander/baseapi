<?php

namespace scripts;

use timanthonyalexander\BaseApi\module\DependencyInjection\DIContainer;
use timanthonyalexander\BaseApi\module\QueryBuilder\QueryBuilder;
use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../config/executedMigrations.json')) {
    unlink(__DIR__ . '/../config/executedMigrations.json');
}


$dicontainer = new DIContainer();
$systemConfig = $dicontainer->get(SystemConfig::class);

assert($systemConfig instanceof SystemConfig);

$db = $systemConfig->getConfigItem('db')['database'] ?? throw new \Exception('No database configured');

$queryBuilder = QueryBuilder::create()
    ->reset()
    ->dropDB($db);

$queryBuilder = QueryBuilder::create()
    ->reset()
    ->createDB($db);

$queryBuilder = QueryBuilder::create()
    ->reset()
    ->useDB($db);
