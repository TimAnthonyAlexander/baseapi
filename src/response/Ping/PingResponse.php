<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\response\Ping;

use timanthonyalexander\BaseApi\model\AbstractResponse\AbstractResponseModel;

class PingResponse extends AbstractResponseModel
{
    public string $ping;
}
