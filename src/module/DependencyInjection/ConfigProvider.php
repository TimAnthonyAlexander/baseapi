<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\module\DependencyInjection;

use timanthonyalexander\BaseApi\model\Translation\TranslationModel;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            TranslationModel::class => static function () {
                return new TranslationModel();
            },
        ];
    }
}
