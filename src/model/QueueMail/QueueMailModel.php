<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\QueueMail;

use DateTimeImmutable;
use timanthonyalexander\BaseApi\model\Mail\MailModel;

class QueueMailModel extends MailModel
{
    public string $sendDate;
    public bool $sent = false;

    public static function getMailsUnsent(): array
    {
        return self::getAllByCustom('sent', '0', useCache: false);
    }

    public static function createFromMail(
        MailModel $mail,
        DateTimeImmutable $sendDate,
    ): static {
        $static = self::create();
        $static->to = $mail->to;
        $static->from = $mail->from;
        $static->fromName = $mail->fromName;
        $static->subject = $mail->subject;
        $static->template = $mail->template;
        $static->signature = $mail->signature;
        $static->body = $mail->body;
        $static->sendDate = $sendDate->format('Y-m-d H:i:s');

        return $static;
    }
}
