<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\interface;

interface ResponseInterface
{
    public static function create(): static;
}
