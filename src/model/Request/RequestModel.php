<?php

namespace timanthonyalexander\BaseApi\model\Request;

use timanthonyalexander\BaseApi\model\Data\DataModel;

class RequestModel extends DataModel
{
    public readonly array $userParams;

    public function __construct(
        public readonly string $route,
        public array $get,
        public array $post,
        public array $files,
        public readonly array $headers,
        public readonly array $cookies,
    ) {
        $userParams = $this->get;
        $userParams = array_merge($userParams, $this->post);
        $userParams = array_merge($userParams, $this->files);
        $userParams = array_merge($userParams, $this->headers);
        $userParams = array_merge($userParams, $this->cookies);

        if (!isset($userParams['language'])) {
            $userParams['language'] = 'english';
        }

        $this->userParams = $userParams;
    }
}
