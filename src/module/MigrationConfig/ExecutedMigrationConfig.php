<?php

namespace timanthonyalexander\BaseApi\module\MigrationConfig;

use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;

class ExecutedMigrationConfig extends SystemConfig
{
    protected const file = 'config/executedMigrations.json';
}
