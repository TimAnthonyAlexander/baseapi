<?php

declare(strict_types=1);

namespace timanthonyalexander\BaseApi\model\Verification;

use timanthonyalexander\BaseApi\model\Entity\EntityModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\UserState\UserState;

class VerificationModel extends EntityModel
{
    public string $user = '';

    public function __construct(string $token)
    {
        parent::__construct($token);
    }

    public static function createVerification(UserModel $user): self
    {
        $token = substr(md5(UserState::encrypt((string) microtime(true))), 10, 6);
        $verification = new self($token);
        $verification->user = $user->id;
        $verification->save();

        return $verification;
    }

    public function getUser(): UserModel
    {
        return new UserModel($this->user);
    }

    public static function verifyToken(string $token): bool
    {
        if (EnvService::isDev()) {
            $token = 'dev';
        }

        $verification = new self($token);

        if (!$verification->exists()) {
            return false;
        }

        $user = $verification->getUser();

        if (!$user->isVerified) {
            $user->isVerified = true;
            $user->role = 'user';
            $user->save();
        }

        $verification->delete();

        UserState::setState($user);

        return true;
    }
}
