<?php

namespace timanthonyalexander\BaseApi\model\Response;

use timanthonyalexander\BaseApi\model\Data\DataModel;
use timanthonyalexander\BaseApi\model\Header\HeaderModel;
use timanthonyalexander\BaseApi\module\UserState\UserState;

class ResponseModel extends DataModel
{
    public int $status = 200;
    public ?string $responseMessage = null;
    public string $route = '';
    public ?UserState $userState;
    public array $data = [];
    public HeaderModel $headers;
    public int $responseTime = 0;
    public array $trace = [];
    public string $sessId;
    public array $params = [];
    public array $profiler = [];
    public array $queries = [];
    public int $retries = 0;

    public function __construct()
    {
        $this->headers = new HeaderModel();
        $this->userState = UserState::getState();
    }

    public function toArray(): array
    {
        $array = get_object_vars($this);

        unset($array['headers']);

        return $array;
    }
}
