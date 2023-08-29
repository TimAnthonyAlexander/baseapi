<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\controller\Ping;

use timanthonyalexander\BaseApi\controller\Abstract\AbstractController;
use timanthonyalexander\BaseApi\module\ParameterTools\ParameterTools;
use timanthonyalexander\BaseApi\response\Ping\PingResponse;

class PingController extends AbstractController
{
    public string $pingMessage;

    public function getAction(): void
    {
        $this->data([
            'ping' => 'pong',
        ]);
    }

    public function postAction(): void
    {
        $pingMessage = (string) ParameterTools::clearText($this->pingMessage);

        $this->data([
            'ping' => $pingMessage,
        ]);
    }

    public function createResponseModel(): PingResponse
    {
        return new PingResponse();
    }
}
