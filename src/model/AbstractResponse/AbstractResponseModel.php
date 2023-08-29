<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\AbstractResponse;

use timanthonyalexander\BaseApi\interface\ResponseInterface;
use timanthonyalexander\BaseApi\model\Data\DataModel;

abstract class AbstractResponseModel extends DataModel implements ResponseInterface
{
    public static function create(): static
    {
        return new static();
    }
}
