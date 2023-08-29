<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\User;

use timanthonyalexander\BaseApi\model\Entity\EntityModel;
use timanthonyalexander\BaseApi\model\Mail\MailModel;
use timanthonyalexander\BaseApi\model\Translation\TranslationModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\Mailer\Mailer;

class NotificationModel extends EntityModel
{
    public string $user;
    public string $title;
    public string $message;
    public bool $isRead = false;
    public string $created; // Y-m-d H:i:s

    public static function getReadNotificationsForUser(UserModel $user): array
    {
        $all = self::getAllByCustom('user', $user->username);

        $read = [];
        foreach ($all as $notification) {
            assert($notification instanceof self);
            if ($notification->isRead) {
                $read[] = $notification;
            }
        }

        return $read;
    }

    public static function getUnreadNotificationsForUser(UserModel $user): array
    {
        $all = self::getAllByCustom('user', $user->username);

        $unread = [];
        foreach ($all as $notification) {
            assert($notification instanceof self);
            if (!$notification->isRead) {
                $unread[] = $notification;
            }
        }

        return $unread;
    }

    public static function getNotificationsForUser(string $user): array
    {
        return self::getAllByCustom('user', $user, true);
    }

    public static function sendNotification(
        UserModel $user,
        string $title,
        string $message,
        bool $sendMail = true,
    ): void {
        $notification = new NotificationModel(uniqid("coan_", true));
        $notification->user = $user->username;
        $notification->title = $title;
        $notification->message = $message;
        $notification->save();

        if ($sendMail) {
            $mail = new MailModel(base64_encode(uniqid("coam_", true)));
            $mail->fromName = 'baseapi';
            $mail->to = $user->email;
            $mail->subject = $title;
            $mail->body = $message;
            $mail->save();

            Mailer::sendTemplated($mail);
        }
    }

    public static function sendUserNotification(
        UserModel $user,
        bool $sendMail = true,
        string $title = '',
        string $message = '',
        array $replacements = [],
    ): void {
        $language = $user->language;

        $title = TranslationModel::getTranslation($title, forceLanguage: $language);
        $message = TranslationModel::getTranslation($message, forceLanguage: $language);

        // Add curly braces around the keys
        foreach ($replacements as $key => $value) {
            $replacements["{" . $key . "}"] = $value;
            unset($replacements[$key]);
        }

        $title = str_replace(array_keys($replacements), array_values($replacements), $title);
        $message = str_replace(array_keys($replacements), array_values($replacements), $message);

        $notification = new NotificationModel(uniqid("coan_", true));
        $notification->user = $user->username;
        $notification->title = $title;
        $notification->message = $message;
        $notification->save();

        if ($sendMail) {
            $mail = new MailModel(base64_encode(uniqid("coam_", true)));
            $mail->fromName = 'baseapi';
            $mail->to = $user->email;
            $mail->subject = $title;
            $mail->body = $message;
            $mail->save();

            Mailer::sendTemplated($mail, true, true);
        }
    }

    public static function readAllNotificationsForUser(UserModel $user): void
    {
        $notifications = self::getUnreadNotificationsForUser($user);

        foreach ($notifications as $notification) {
            assert($notification instanceof self);
            $notification->isRead = true;
            $notification->save();
        }
    }
}
