<?php

namespace timanthonyalexander\BaseApi\model\User;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use timanthonyalexander\BaseApi\model\Entity\EntityModel;
use timanthonyalexander\BaseApi\model\Mail\MailModel;
use timanthonyalexander\BaseApi\model\Subscription\SubscriptionModel;
use timanthonyalexander\BaseApi\model\Translation\TranslationModel;
use timanthonyalexander\BaseApi\model\UserSkill\UserSkillModel;
use timanthonyalexander\BaseApi\model\Verification\VerificationModel;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\Mailer\Mailer;
use timanthonyalexander\BaseApi\module\PermissionConfig\PermissionConfig;
use timanthonyalexander\BaseApi\module\QueryBuilder\QueryBuilder;
use timanthonyalexander\BaseApi\module\UriService\UriService;
use timanthonyalexander\BaseApi\module\UserState\UserState;

class UserModel extends EntityModel
{
    public string $name = '';
    public string $description = 'New to baseapi';
    public string $email = '';
    public string $role = 'guest';
    public bool $isVerified = false;
    public string $language = 'english';
    public string $image = 'default.png';
    public int $views = 0;
    public bool $cli = false;
    public string $redirect = '/home';
    public string $created; // Y-m-d H:i:s
    public string $lastonline; // Y-m-d H:i:s

    public function __construct(public string $username)
    {
        parent::__construct($this->username);
    }

    public function hasPermission(string $permission): bool
    {
        if (in_array('admin', self::getRolePermissions($this->role), true)) {
            return true;
        }

        return in_array($permission, self::getRolePermissions($this->role), true);
    }

    public static function getRolePermissions(string $roleP): array
    {
        $permConfig = new PermissionConfig();
        $roleSettings = $permConfig->getConfigItem(
            $roleP,
            [
                'permissions' => [],
                'inherits' => ['guest'],
            ]
        );

        $permissions = $roleSettings['permissions'];

        foreach ($roleSettings['inherits'] as $role) {
            /**
             * @noinspection SlowArrayOperationsInLoopInspection
             */
            $permissions = array_merge($permissions, self::getRolePermissions($role));
        }

        return $permissions;
    }

    /**
     * @param array<int, string> $skills
     */
    public static function signUp(
        string $name,
        string $email,
        bool $subscribe = false,
        bool $sendMail = true,
        array $skills = [],
    ): UserModel {
        $validator = new EmailValidator();
        if (!$validator->isValid($email, new RFCValidation())) {
            throw new \Exception('Email not valid: ' . $email);
        }

        $user = new UserModel(uniqid("clu", true));
        $user->name = $name;
        $user->email = $email;
        $user->role = 'guest';
        $user->save();

        NotificationModel::sendNotification(
            $user,
            sprintf(
                TranslationModel::getTranslation('signup.welcome'),
                $user->name,
            ),
            sprintf(
                TranslationModel::getTranslation('signup.welcome.message'),
                $user->name,
            ),
        );

        foreach ($skills as $skill) {
            UserSkillModel::addSkillForUser($user->id, $skill);
        }

        if (!$sendMail) {
            return $user;
        }

        self::verifyEmail($user);

        if ($subscribe) {
            SubscriptionModel::subscribeUser($user);
        }

        UserState::setState($user);

        return $user;
    }

    public static function signIn(
        string $email
    ): UserModel {
        $validator = new EmailValidator();
        if (!$validator->isValid($email, new RFCValidation())) {
            throw new \Exception('Email not valid: ' . $email);
        }

        $user = UserModel::getByEmail($email);

        self::verifyEmail($user);

        return $user;
    }

    public static function existsByEmail(string $email): bool
    {
        return static::existsByCustom('email', $email);
    }

    public static function getByEmail(string $email): ?UserModel
    {
        return static::getFirstByCustom('email', $email);
    }

    public static function getById(string $id): ?UserModel
    {
        return new UserModel($id);
    }

    public function redirect(string $url): void
    {
        $this->redirect = $url;
        $this->save();
    }

    public function upgrade(): void
    {
        if ($this->role === 'user') {
            $this->role = 'premium';
            $this->save();
        }
    }

    public static function getOnlineUsers(): array
    {
        $query = QueryBuilder::create()
            ->reset()
            ->select('user', true, ['id'])
            ->advancedWhere(
                'user',
                'lastonline > DATE_SUB(NOW(), INTERVAL 5 MINUTE)',
                custom: true,
                customString: 'lastonline > DATE_SUB(NOW(), INTERVAL 5 MINUTE)',
            )
            ->run();

        $users = [];

        foreach ($query as $user) {
            $users[] = UserModel::getById($user['id']);
        }

        return $users;
    }

    public static function getOnlineUserCount(int $minutes = 5): int
    {
        $query = QueryBuilder::create()
            ->reset()
            ->selectCount('user', true, ['id'])
            ->advancedWhere(
                'user',
                'lastonline > DATE_SUB(NOW(), INTERVAL ' . $minutes . ' MINUTE)',
                custom: true,
                customString: 'lastonline > DATE_SUB(NOW(), INTERVAL ' . $minutes . ' MINUTE)',
                removeQuotes: true,
            )
            ->run(true, true);

        return $query;
    }

    public function isOnline(int $minutes = 5): bool
    {
        $lastonline = $this->lastonline ?? '2000-01-01 00:00:00';

        // Calculate the difference in seconds between the two timestamps.
        //2023-06-08 16:55:33 as an example of lastonline
        $diff = strtotime(date('Y-m-d H:i:s')) - strtotime($lastonline);

        // If the difference is less than the number of seconds in $minutes minutes, the user is online.
        return $diff < ($minutes * 60);
    }

    /**
     * @param  UserModel $user
     * @return void
     */
    public static function verifyEmail(UserModel $user): void
    {
        $verification = VerificationModel::createVerification($user);

        if (EnvService::isDev()) {
            $adminVerification = VerificationModel::createVerification($user);
            $adminVerification->id = 'dev';
            $adminVerification->save();
        }

        $token = $verification->id;

        $uriService = new UriService('/verify/' . $token);
        $uri = $uriService->fromEnv()->build();

        $mail = MailModel::create();
        $mail->to = $user->email;
        $mail->from = TranslationModel::getTranslation('verify_email_from', ucFirst: false, forceLanguage: $user->language);
        $mail->fromName = TranslationModel::getTranslation('verify_email_from_name', forceLanguage: $user->language);
        $mail->subject = TranslationModel::getTranslation('verify_email_subject', forceLanguage: $user->language);
        $mail->body = sprintf(TranslationModel::getTranslation('verify_email_body', forceLanguage: $user->language), $uri, $token);
        $mail->save();

        Mailer::sendTemplated(
            $mail,
        );
    }
}
