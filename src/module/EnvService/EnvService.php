<?php

namespace timanthonyalexander\BaseApi\module\EnvService;

use Exception;
use timanthonyalexander\BaseApi\module\DependencyInjection\DIContainer;
use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;

class EnvService extends SystemConfig
{
    public static function getEnv(): string
    {
        $dicontainer = new DIContainer();
        return $dicontainer->get(static::class)->getConfigItem('env', 'dev');
    }

    public static function getFrontendDomain(): string
    {
        $env = self::getEnv();

        if (!defined('STDIN') && (getallheaders()['isApp'] ?? false)) {
            return match ($env) {
                'dev' => 'http://dev.baseapi-test.app:62003',
                'latest' => 'http://dev.baseapi-test.app:62003',
                'integration' => 'https://app.int.example.com',
                'staging' => 'https://app.staging.example.com',
                'production' => 'https://app.example.com',
                default => throw new Exception('Unknown env: ' . $env),
            };
        }

        return match ($env) {
            'dev' => 'http://dev.baseapi-test.app:62002',
            'latest' => 'http://dev.baseapi-test.app:62002',
            'integration' => 'https://int.example.com',
            'staging' => 'https://staging.example.com',
            'production' => 'https://example.com',
            default => throw new Exception('Unknown env: ' . $env),
        };
    }

    public static function isDev(): bool
    {
        return self::getEnv() === 'dev';
    }

    public static function isLatest(): bool
    {
        return self::getEnv() === 'latest';
    }

    public static function isProduction(): bool
    {
        return self::getEnv() === 'production';
    }

    public static function isStaging(): bool
    {
        return self::getEnv() === 'staging';
    }

    public static function isIntegration(): bool
    {
        return self::getEnv() === 'integration';
    }
}
