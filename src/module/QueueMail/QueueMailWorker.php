<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\module\QueueMail;

use timanthonyalexander\BaseApi\model\QueueMail\QueueMailModel;
use timanthonyalexander\BaseApi\module\Mailer\Mailer;

date_default_timezone_set('Europe/Berlin');

class QueueMailWorker
{
    public function __construct()
    {
        while (true) {
            $mails = QueueMailModel::getMailsUnsent();
            foreach ($mails as $mail) {
                assert($mail instanceof QueueMailModel);

                $sendDate = $mail->sendDate;

                $sendDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $sendDate);
                $now = new \DateTimeImmutable();

                if ($sendDate > $now) {
                    continue;
                }

                $success = Mailer::sendTemplated($mail, true, true);

                if ($success === true) {
                    print "Sent mail {$mail->id}\n";
                    $mail->sent = true;
                    $mail->save();
                } else {
                    print "Did not send mail due to: " . $success . PHP_EOL;
                }
            }
        }
    }
}
