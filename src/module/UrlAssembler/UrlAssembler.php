<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\module\UrlAssembler;

use timanthonyalexander\BaseApi\module\UriService\UriService;

class UrlAssembler
{
    public static function getBaseUrlFrontend(): UriService
    {
        return (new UriService())->fromEnv();
    }

    public static function getBaseUrlAPI(): UriService
    {
        return (new UriService())->fromEnv(false);
    }
}
