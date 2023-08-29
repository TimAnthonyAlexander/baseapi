<?php

namespace timanthonyalexander\BaseApi\module\SystemClock;

use DateTimeImmutable;
use Exception;

class SystemClock
{
    /**
     * @throws Exception
     */
    public static function create(
        string $time = 'now',
        string $timezone = 'Europe/Berlin',
    ): DateTimeImmutable {
        return new DateTimeImmutable($time, new \DateTimeZone($timezone));
    }
}
