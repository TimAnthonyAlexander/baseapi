<?php

namespace timanthonyalexander\BaseApi\model\DBCache;

use timanthonyalexander\BaseApi\model\Entity\EntityModel;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\InstantCache\InstantCache;
use timanthonyalexander\BaseApi\module\Page\Page;

class DBCache extends EntityModel
{
    public string $value;
    public string $created; // Y-m-d H:i:s
    public string $expiry = '-1 day';

    public static function set(string $key, mixed $value, string $expiry = '-1 day'): void
    {
        $value = serialize($value);

        $cache = new self($key);
        $cache->value = base64_encode($value);
        $cache->created = date('Y-m-d H:i:s');
        $cache->expiry = $expiry;
        $cache->save();

        InstantCache::set($key, $value);
    }

    public static function get(string $key, string $default = null): mixed
    {
        if (InstantCache::isset($key)) {
            return InstantCache::get($key);
        }

        $cache = new self($key);
        if (!self::isset($key)) {
            self::set($key, $default);
            return $default;
        }

        $value = base64_decode($cache->value);
        $value = unserialize($value);

        InstantCache::set($key, $value);

        return $value;
    }

    public static function remove(string $key): void
    {
        $cache = new self($key);
        if ($cache->exists()) {
            $cache->delete();
        }

        InstantCache::delete($key);
    }

    public static function isset(string $key): bool
    {
        if (($_GET['nocache'] ?? '0') === '1') {
            return false;
        }

        if (InstantCache::isset($key)) {
            return true;
        }

        $self = new self($key);
        if ($self->exists()) {
            if (strtotime($self->created) < strtotime($self->expiry)) {
                $self->delete();
                return false;
            }
            return true;
        }
        return false;
    }

    public static function cleanUpOlderThan(string $time = '-1 day'): void
    {
        $caches = self::getAll();
        foreach ($caches as $cache) {
            if (strtotime($cache['created']) < strtotime($time)) {
                $self = new self($cache['id']);
                $self->delete();
            }
        }
    }

    public static function cleanAll(): void
    {
        $caches = self::getAll();
        foreach ($caches as $cache) {
            $self = new self($cache['id']);
            $self->delete();
        }
    }
}
