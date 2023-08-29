<?php

/**
 * @noinspection ALL
 */

namespace timanthonyalexander\BaseApi\module\Api;

use Exception;
use timanthonyalexander\BaseApi\controller\Abstract\AbstractController;
use timanthonyalexander\BaseApi\controller\Base\BaseController;
use timanthonyalexander\BaseApi\model\Request\RequestModel;
use timanthonyalexander\BaseApi\model\Response\ResponseModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\DependencyInjection\DIContainer;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\Page\Page;
use timanthonyalexander\BaseApi\module\Profiler\Profiler;
use timanthonyalexander\BaseApi\module\RouteConfig\RouteConfig;

class Api
{
    public string $route;
    public RequestModel $request;

    /**
     * @param  RequestModel $request
     * @return void
     */
    public function setRequest(RequestModel $request): void
    {
        $this->request = $request;
    }

    /**
     * @param  UserModel|null $user
     * @return ResponseModel
     * @throws Exception
     */
    public function send(?UserModel $user = null): ResponseModel
    {
        $this->cors();

        set_error_handler(
            static function ($errno, $errstr, $errfile, $errline): bool {
                Page::writeLog(new Exception($errstr . $errfile . ':' . $errline, $errno));
                return true;
            }
        );

        Page::log('API call to ' . $this->route);

        $routeConfig = new RouteConfig();

        if (!$routeConfig->existsConfigItem($this->route)) {
            return Page::create404ExceptionResponse();
        }

        $controllers = $routeConfig->getConfigItem($this->route);

        if ($controllers === null) {
            return Page::createExceptionResponse();
        }

        $response = new ResponseModel();
        $response->route = $this->route;

        if (EnvService::isDev()) {
            $response->params = array_merge($_GET, $_POST, $_FILES, $_COOKIE);
        }

        foreach ($controllers as $controller) {
            $timeStart = microtime(true);
            $currentResponse = $this->sendToController($controller, $user);
            $timeEnd = microtime(true);
            $milliseconds = ($timeEnd - $timeStart) * 1000;
            Page::log('[[ ' . $controller . ' ]]  GET[' . json_encode($this->request->get) . '] - POST[' . json_encode($this->request->post) . '] --- ' . $milliseconds . 'ms');
            if (!EnvService::isProduction()) {
                Page::log('    Response: ' . PHP_EOL . json_encode($currentResponse->data, JSON_PRETTY_PRINT));
            }

            if ($currentResponse->status !== 200) {
                Page::log('[' . $currentResponse->status . ' ' . $currentResponse->responseMessage . ']');
                $response->status = $currentResponse->status;
            } else {
                Page::log('[200 OK]');
            }

            if ($currentResponse->responseMessage !== null) {
                $response->responseMessage = $currentResponse->responseMessage;
            }
            Page::log('---------------------------------------------------------------------------');

            $response->data = array_merge_recursive($response->data, $currentResponse->data);
            $response->headers->headers = array_merge($response->headers->headers, $currentResponse->headers->headers);
            $response->responseTime += $currentResponse->responseTime;
            $response->sessId = $currentResponse->sessId;
            $response->trace[] = $currentResponse->trace;
            $response->profiler = array_merge($response->profiler, $currentResponse->profiler);
            $response->queries = array_merge($response->queries, $currentResponse->queries);
        }

        $response->profiler = Profiler::calculatePercentages($response->profiler);

        return $response;
    }

    /**
     * @param  string         $controller
     * @param  UserModel|null $user
     * @return ResponseModel
     * @throws Exception
     */
    private function sendToController(string $controller, UserModel $user = null): ResponseModel
    {
        $class = sprintf('timanthonyalexander\BaseApi\\controller\\%s\\%sController', $controller, $controller);

        if (!class_exists($class)) {
            throw new Exception(sprintf('Class %s does not exist.', $class));
        }

        $dicontainer = new DIContainer();

        $object = new $class($this->request, $dicontainer, $user);
        assert($object instanceof AbstractController);

        return $object->response;
    }

    /**
     * @param  string $route
     * @return void
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    private function cors(): void
    {
        $origin = EnvService::getFrontendDomain();

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
    }
}
