<?php

namespace public;

use timanthonyalexander\BaseApi\module\Page\Page;

require __DIR__ . '/../vendor/autoload.php';

$page = new Page();
$response = $page->route();

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$headers = $response->headers->headers;

foreach ($headers as $header) {
    header($header);
}
http_response_code($response->status);

$jsonString = json_encode($response->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

print $jsonString;
