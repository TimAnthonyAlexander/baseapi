<?php

namespace timanthonyalexander\BaseApi\model\Optional;

use timanthonyalexander\BaseApi\model\Data\DataModel;

class OptionalModel extends DataModel
{
    private function __construct(
        private readonly array $data
    ) {
    }

    public static function create(array $data = ['one' => 'value', 'two' => null]): static
    {
        return new static($data);
    }

    public function hasNotNull(): bool
    {
        return $this->getNotNull() !== null;
    }

    public function whichIsNotNull(): ?string
    {
        foreach ($this->data as $key => $value) {
            if ($value !== null) {
                return $key;
            }
        }

        return null;
    }

    public function getNotNull(): mixed
    {
        foreach ($this->data as $key => $param) {
            if ($param !== null) {
                return $param;
            }
        }

        return null;
    }
}
