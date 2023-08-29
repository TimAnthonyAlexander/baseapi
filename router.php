<?php

$parseUri = parse_url($_SERVER['REQUEST_URI']);

if (file_exists(__DIR__ . '/public' . ($parseUri['path'] ?? ''))) {
    return false;
} else {
    include __DIR__ . '/public/index.php';
}
