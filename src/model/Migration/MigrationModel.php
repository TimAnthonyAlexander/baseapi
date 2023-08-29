<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\Migration;

use timanthonyalexander\BaseApi\model\Data\DataModel;

class MigrationModel extends DataModel
{
    public function __construct(
        public string $do = '',
        public string $undo = '',
        public string $name = '',
    ) {
    }
}
