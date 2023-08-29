<?php

declare(strict_types=1);

namespace scripts;

use timanthonyalexander\BaseApi\module\QueueMail\QueueMailWorker;

require_once __DIR__ . '/../vendor/autoload.php';

$mailWorker = new QueueMailWorker();
