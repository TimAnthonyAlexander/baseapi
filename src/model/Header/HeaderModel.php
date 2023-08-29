<?php

namespace timanthonyalexander\BaseApi\model\Header;

use timanthonyalexander\BaseApi\model\Data\DataModel;

class HeaderModel extends DataModel
{
    public array $headers = [];

    public function addHeader(string $headerKey, string $headerValue): self
    {
        $this->headers[] = sprintf('%s: %s', $headerKey, $headerValue);
        return $this;
    }

    public function clearHeaders(): self
    {
        $this->headers = [];
        return $this;
    }

    public function allHeaders(): string
    {
        return implode("\r", $this->headers);
    }

    public function toArray(): array
    {
        return $this->headers;
    }
}
