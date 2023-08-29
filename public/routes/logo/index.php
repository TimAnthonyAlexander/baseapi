<?php


if (isset($_GET['wortbildmarke'])) {
    $file = __DIR__ . '/wortbildmarke.png';
} elseif (isset($_GET['wortbildmarke_light'])) {
    $file = __DIR__ . '/wortbildmarke_light.png';
} else {
    $file = __DIR__ . '/favicon.png';
}


header('Content-Type: image/png');
header('Content-Length: ' . filesize($file));
readfile($file);
