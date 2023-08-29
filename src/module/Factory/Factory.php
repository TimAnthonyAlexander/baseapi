<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\module\Factory;

use timanthonyalexander\BaseApi\module\InstantCache\InstantCache;

class Factory
{
    /*
     * @return object
     */
    public static function getClass(
        string $class = Factory::class,
        mixed ...$params
    ): mixed {
        $paramsHash = md5(json_encode($params, JSON_THROW_ON_ERROR) ?: '');
        $classInstance = InstantCache::isset(sprintf('factories_%s_%s', $class, $paramsHash)) ? clone InstantCache::get(sprintf('factories_%s_%s', $class, $paramsHash)) : new $class(...$params);
        if (!InstantCache::isset(sprintf('factories_%s_%s', $class, $paramsHash))) {
            InstantCache::set(sprintf('factories_%s_%s', $class, $paramsHash), $classInstance);
        }
        assert($classInstance instanceof $class);
        return $classInstance;
    }
}
