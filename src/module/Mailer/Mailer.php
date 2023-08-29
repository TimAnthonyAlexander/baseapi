<?php

namespace timanthonyalexander\BaseApi\module\Mailer;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use timanthonyalexander\BaseApi\model\Mail\MailModel;
use timanthonyalexander\BaseApi\model\QueueMail\QueueMailModel;
use timanthonyalexander\BaseApi\module\Profiler\Profiler;
use timanthonyalexander\BaseApi\module\SystemConfig\SystemConfig;
use timanthonyalexander\BaseApi\module\UriService\UriService;

/**
 * @copyright Tim Anthony Alexander @ baseapi
 */
class Mailer
{
    public string $sign_cert_file = '';
    public string $sign_key_file  = '';
    public string $sign_key_pass  = '';

    /**
     * @TODO   Describe sendTemplated
     * @param  MailModel $mail
     * @return bool|string
     */
    public static function sendTemplated(
        MailModel $mail,
        bool $sendOnDev = false,
        bool $cliOverride = false,
    ): bool | string {
        Profiler::start();
        if ($sendOnDev === false) {
        }

        $body = '';

        $body = str_replace(
            '[TEXT]',
            $mail->body,
            (string)self::loadTemplate($mail->template)
        );

        $body = str_replace(
            '[SIGNATURE]',
            $mail->signature,
            $body,
        );

        $uriService = new UriService('/newsletter/unsubscribe/' . $mail->to . '/default');
        $uriService->fromEnv();

        $unsubscribeUrl = $uriService->build();

        $body = str_replace(
            '[UNSUBSCRIBE_URL]',
            $unsubscribeUrl,
            $body,
        );

        $mail->save();

        // If this is cli, we don't want to send mails
        if (!$cliOverride && php_sapi_name() === 'cli') {
            return true;
        }

        $return = self::sendBasic(
            $mail->to,
            $mail->from,
            $mail->subject,
            $body,
            $mail->fromName,
        );

        Profiler::stop();

        return $return;
    }

    /**
     * @TODO   Describe sendBasic
     * @param  string|array $to
     * @param  string       $from
     * @param  string       $subject
     * @param  string       $message
     * @param  string       $fromName
     * @return bool | string
     */
    public static function sendBasic(
        string|array $to,
        string $from,
        string $subject,
        string $message,
        string $fromName,
    ): bool | string {
        $smtpData = self::getSMTPData();
        $phpMailer = new PHPMailer(false);
        $phpMailer->isSMTP();
        $phpMailer->CharSet = 'UTF-8';
        $phpMailer->SMTPKeepAlive = true;
        $phpMailer->Host = $smtpData['host'];
        $phpMailer->SMTPDebug = $smtpData['debug'];
        $phpMailer->SMTPAuth = true;
        $phpMailer->Port = 587;
        $phpMailer->SMTPSecure = 'tls';
        $phpMailer->Username = $smtpData['username'];
        $phpMailer->Password = $smtpData['password'];
        $phpMailer->isHTML();
        $phpMailer->clearAddresses();

        $phpMailer->Body = $message;

        $pathToCrt = __DIR__ . '/../../../crt';

        $dkimPrivate = $pathToCrt . '/dkim/private.key';

        $phpMailer->DKIM_domain = 'example.com';
        $phpMailer->DKIM_selector = 's1';
        $phpMailer->DKIM_private = $dkimPrivate;

        $phpMailer->sign(
            $pathToCrt . '/certificate.crt',
            $pathToCrt . '/private_unencrypted.key',
            '',
        );

        try {
            $phpMailer->setFrom($from, $fromName);
            $phpMailer->Subject = $subject;
            if (is_array($to)) {
                foreach ($to as $toAddress) {
                    $phpMailer->addAddress($toAddress);
                    self::send($phpMailer);
                    $phpMailer->clearAllRecipients();
                }
                return true;
            }
            $phpMailer->addAddress($to);
            return self::send($phpMailer);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @throws Exception
     */
    private static function send(PHPMailer $phpMailer): bool
    {
        $smtpData = self::getSMTPData();

        if (!$phpMailer->send()) {
            throw new Exception($phpMailer->ErrorInfo);
        }
        self::logSent(true, $phpMailer, $smtpData);
        return true;
    }

    /**
     * @throws \Exception
     */
    private static function logSent(bool $success, PHPMailer $phpMailer, array $smtpData = []): void
    {
        $log = fopen(__DIR__ . '/../../emaillog.txt', 'ab') ?: throw new \Exception('Could not open email log');
        fwrite($log, date('Y-m-d H:i:s') . ' ' . $success . ' ' . $phpMailer->Username . ' ' . $phpMailer->Password . ' ' . $phpMailer->Host . ' ' . $phpMailer->Port . ' ' . $phpMailer->SMTPDebug . ' ' . json_encode($phpMailer->getToAddresses(), 128) . ' ' . json_encode($smtpData) . PHP_EOL);
        fclose($log);
    }

    /**
     * @TODO   Describe loadTemplate
     * @param  string $name
     * @return bool|string
     */
    public static function loadTemplate(
        string $name
    ): bool|string {
        return file_get_contents(sprintf(__DIR__ . '/../../../config/templates/%s.html', $name));
    }

    /**
     * @return array
     */
    public static function getSMTPData(): array
    {
        return (new SystemConfig())->getConfigItem(
            'smtp',
            [
                'host' => 'smtp.example.com',
                'username' => 'username@example.com',
                'password' => 'password',
                'from' => '',
                'fromName' => '',
                'debug' => 0,
            ]
        );
    }
}
