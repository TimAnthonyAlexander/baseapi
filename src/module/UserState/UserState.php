<?php

namespace timanthonyalexander\BaseApi\module\UserState;

use timanthonyalexander\BaseApi\model\Data\DataModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\InstantCache\InstantCache;
use timanthonyalexander\BaseApi\module\Page\Page;

class UserState extends DataModel
{
    private const NAMESPACE = 'ae77f78b-0f27-494c-8dd5-1eacce2717c9';
    private const IV = '2737d58a-9e0b-43';

    public UserModel $userModel;
    public bool $isLogin = false;

    public function __construct()
    {
        $this->isLogin = self::isLogin();
    }

    public static function setState(UserModel $userModel): void
    {
        $_SESSION['baseapipi']['user'] = $userModel->id;
    }

    public static function getState(): self
    {
        try {
            if (!isset($_SESSION['baseapipi']['user'])) {
                $self = new self();
                $self->userModel = new UserModel('guest');
                return $self;
            }

            $userState = new self();
            $userState->userModel = new UserModel($_SESSION['baseapipi']['user'] ?? '');
        } catch (\Exception $e) {
            Page::writeLog($e);

            $userState = self::getState();
        }

        return $userState;
    }

    public static function isLogin(): bool
    {
        return isset($_SESSION['baseapipi']['user']);
    }

    public function isLoggedIn(): bool
    {
        return UserModel::existsById($this->userModel->id);
    }

    public static function decrypt(string $string, string $salt = null): string
    {
        if (InstantCache::isset(sprintf('decrypt_%s', $string))) {
            return InstantCache::get(sprintf('decrypt_%s', $string));
        }

        $decrypted = openssl_decrypt(
            $string,
            'aes-256-ctr',
            $salt ?? self::NAMESPACE,
            iv: self::IV,
        );

        InstantCache::set(sprintf('decrypt_%s', $string), $decrypted);

        return $decrypted ?: '';
    }

    public static function encrypt(string $string, string $salt = null): string
    {
        if (InstantCache::isset(sprintf('encrypt_%s', $string))) {
            return InstantCache::get(sprintf('encrypt_%s', $string));
        }

        $encrypted = openssl_encrypt(
            $string,
            'aes-256-ctr',
            $salt ?? self::NAMESPACE,
            iv: self::IV,
        );

        InstantCache::set(sprintf('encrypt_%s', $string), $encrypted);

        return $encrypted ?: '';
    }

    public function logout(): void
    {
        unset($_SESSION['baseapipi']['user']);
    }
}
