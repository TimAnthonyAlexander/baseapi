<?php

declare(strict_types=1);

namespace scripts;

use timanthonyalexander\BaseApi\model\Translation\TranslationModel;
use timanthonyalexander\BaseApi\model\User\NotificationModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\model\WeeklyDigest\WeeklyDigestModel;
use timanthonyalexander\BaseApi\module\Interests\Interests;
use timanthonyalexander\BaseApi\module\UrlAssembler\UrlAssembler;

ini_set('memory_limit', '1G');
ini_set('max_execution_time', '0');

require_once __DIR__ . '/../../vendor/autoload.php';

$weekly = WeeklyDigestModel::createForNow();
if ($weekly->isSent) {
    echo "Weekly digest for week {$weekly->week} of {$weekly->year} already sent.\n";
    exit(0);
}

$weekly->save();

$users = UserModel::getAll();

$translationModel = new TranslationModel();

foreach ($users as $user) {
    if ($user['cli']) {
        continue;
    }

    $userModel = new UserModel($user['id']);

    $results = (new Interests())
        ->withUser($userModel)
        ->withPage(random_int(1, 5))
        ->withLimit(3)
        ->executeProjects()
        ->getResults();

    if (count($results) < 3) {
        print "Not enough interests found for " . $userModel->name . "." . PHP_EOL;
        continue;
    }

    NotificationModel::sendUserNotification(
        $userModel,
        true,
        'weekly_digest_subject',
        'weekly_digest_body',
        [
            'user' => $userModel->name,
            'interest1title' => htmlspecialchars($results[0]['title']),
            'interest1url' => UrlAssembler::getProjectUrl($results[0]['id']),
            'interest1image' => UrlAssembler::getImageUrl($results[0]['images'][0]->id),
            'interest1description' => htmlspecialchars($results[0]['description']),
            'interest2title' => htmlspecialchars($results[1]['title']),
            'interest2url' => UrlAssembler::getProjectUrl($results[1]['id']),
            'interest2image' => UrlAssembler::getImageUrl($results[1]['images'][0]->id),
            'interest2description' => htmlspecialchars($results[1]['description']),
            'interest3title' => htmlspecialchars($results[2]['title']),
            'interest3url' => UrlAssembler::getProjectUrl($results[2]['id']),
            'interest3image' => UrlAssembler::getImageUrl($results[2]['images'][0]->id),
            'interest3description' => htmlspecialchars($results[2]['description']),
        ]
    );
}

$weekly->isSent = true;
$weekly->save();
