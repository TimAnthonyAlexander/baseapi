<?php

namespace timanthonyalexander\BaseApi\module\Page;

use Exception;
use RuntimeException;
use timanthonyalexander\BaseApi\model\Request\RequestModel;
use timanthonyalexander\BaseApi\model\Response\ResponseModel;
use timanthonyalexander\BaseApi\model\User\NotificationModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\Api\Api;
use Throwable;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;

class Page
{
    public function route(string $overwriteRoute = null): ResponseModel
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $route = $overwriteRoute ?? Page::readRoute();

        $api = new Api();

        $request = new RequestModel($route, $_GET, $_POST, $_FILES, getallheaders(), $_COOKIE);

        $api->setRequest($request);
        $api->setRoute($route);

        try {
            return $this->send($api);
        } catch (Throwable $e) {
            return self::createExceptionResponse($e, $route);
        }
    }

    private function send(Api $api, int $count = 0): ResponseModel
    {
        try {
            $response = $api->send();
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Packets out of order') && $count < 30) {
                $response = $this->send($api, ++$count);
                sleep(1);
            } else {
                throw $e;
            }
        }

        $response->retries = $count;

        return $response;
    }

    public static function createExceptionResponse(Throwable $e = null, string $route = ''): ResponseModel
    {
        $response = new ResponseModel();
        $response->responseMessage = 'An error has occured. Check logs';
        $response->headers->headers[] = 'Content-Type: application/json';
        $response->trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $response->route = $route;
        $response->status = 500;

        if (!EnvService::isProduction()) {
            $response->responseMessage = $e?->getMessage() ?? '';
            $response->data = ['exception' => $e?->getMessage() ?? ''];
        }

        if ($e !== null) {
            self::writeLog($e, $e->getPrevious());
        }

        return $response;
    }

    public static function writeLog(Throwable $e, ?Throwable $previous = null): void
    {
        $log = sprintf('[%s] %s: %s in %s on line %s', date('Y-m-d H:i:s'), $e::class, $e->getMessage(), $e->getFile(), $e->getLine());
        $log .= sprintf(PHP_EOL . 'Additional information:' . PHP_EOL . ' %s', $e->getTraceAsString());
        $log .= sprintf(PHP_EOL . 'Previous exception: %s', $previous !== null ? $previous->getMessage() : 'No previous exception');

        // Write into logs/error.log
        $logFolder = __DIR__ . '/../../../logs/';

        if (!is_dir($logFolder) && !mkdir($logFolder) && !is_dir($logFolder)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $logFolder));
        }

        $logFile = fopen($logFolder . 'errors.log', 'ab') ?: throw new Exception();
        fwrite($logFile, $log . PHP_EOL);
        fclose($logFile);

        NotificationModel::sendNotification(
            UserModel::getByEmail('tim.alexander@example.com') ?? new UserModel('a'),
            'Error on example.com on System ' . EnvService::getEnv(),
            $log,
        );
    }

    public static function log(string $message, string $fileName = 'log.log'): void
    {
        $log = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message);

        // Write into logs/error.log
        $logFolder = __DIR__ . '/../../../logs/';

        if (!is_dir($logFolder) && !mkdir($logFolder) && !is_dir($logFolder)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $logFolder));
        }

        $logFile = fopen($logFolder . $fileName, 'ab') ?: throw new Exception();
        fwrite($logFile, $log . PHP_EOL);
        fclose($logFile);
    }

    public static function create404ExceptionResponse(): ResponseModel
    {
        $response = new ResponseModel();
        $response->responseMessage = 'Page not found';
        $response->headers->headers[] = 'Content-Type: application/json';
        $response->status = 404;

        return $response;
    }

    public static function readRoute(): string
    {
        if (!isset($_GET['route'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            return (string) (parse_url($requestUri ?? '/index.json', PHP_URL_PATH) ?? '/index.json');
        }

        $parsed_url_string = $_GET['route'];

        $parsed_url_string = ltrim((string) $parsed_url_string, '/');
        $parsed_url_string = '/' . $parsed_url_string;
        if ($parsed_url_string === '/') {
            $parsed_url_string = '/index.json';
        }
        return $parsed_url_string;
    }
}
