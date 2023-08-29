<?php

namespace timanthonyalexander\BaseApi\tests\controller;

use Exception;
use timanthonyalexander\BaseApi\module\Api\Api;
use timanthonyalexander\BaseApi\model\Request\RequestModel;
use timanthonyalexander\BaseApi\model\Response\ResponseModel;
use timanthonyalexander\BaseApi\model\User\UserModel;

class MockApi
{
    /**
     * @throws Exception
     */
    public function send(
        string $route,
        array $get = [],
        array $post = [],
        array $files = [],
        array $cookies = [],
        array $server = [],
        bool $login = false,
        string $role = 'user',
    ): ResponseModel {
        $api = new Api();
        $api->setRoute($route);

        $request = new RequestModel(
            $route,
            $get,
            $post,
            $files,
            $cookies,
            $server
        );

        $api->setRequest($request);

        if ($login) {
            $user = self::createMockApiUser($role);
        } else {
            $user = null;
        }

        $response = $api->send($user);

        if ($response->status !== 200) {
            throw new Exception('Non-200-status (' . $response->status . '): ' . json_encode($response, JSON_PRETTY_PRINT));
        }

        return $response;
    }

    public static function createMockApiUser(string $role = 'user'): UserModel
    {
        $user = new UserModel('mockapiuser');
        if ($user->exists() && $user->role === $role) {
            return $user;
        }
        $user->email = 'mockapiuser@example.com';
        $user->isVerified = true;
        $user->role = $role;
        $user->name = 'MockApiUser';
        $user->save();
        return $user;
    }

    // On destruct destroy the user
    public function __destruct()
    {
        $user = new UserModel('mockapiuser');
        if ($user->exists()) {
            $user->delete();
        }
    }
}
