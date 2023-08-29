<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\Mail;

use timanthonyalexander\BaseApi\model\Entity\EntityModel;

class MailModel extends EntityModel
{
    public string $to;
    public string $from = 'no-reply@example.com';
    public string $fromName = 'baseapi';
    public string $subject;
    public string $template = 'newtest';
    public string $signature = 'baseapi-Team';
    public string $body;
    public string $created;
}
