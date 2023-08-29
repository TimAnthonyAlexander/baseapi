<?php

namespace public;

use timanthonyalexander\BaseApi\module\Page\Page;

require __DIR__ . '/../vendor/autoload.php';

$page = new Page();
$response = $page->route('/cloud/file.json');

$headers = $response->headers->headers;

foreach ($headers as $header) {
    header($header);
}

http_response_code($response->status);

print $response->data['image'] ?? '';
