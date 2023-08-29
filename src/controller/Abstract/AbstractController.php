<?php

namespace timanthonyalexander\BaseApi\controller\Abstract;

use Exception;
use timanthonyalexander\BaseApi\interface\V2Controller;
use timanthonyalexander\BaseApi\model\AbstractResponse\AbstractResponseModel;
use timanthonyalexander\BaseApi\model\Request\RequestModel;
use timanthonyalexander\BaseApi\model\Response\ResponseModel;
use timanthonyalexander\BaseApi\model\Translation\TranslationModel;
use timanthonyalexander\BaseApi\model\User\UserModel;
use timanthonyalexander\BaseApi\module\ABTestConfig\ABTestConfig;
use timanthonyalexander\BaseApi\module\DependencyInjection\DIContainer;
use timanthonyalexander\BaseApi\module\EnvService\EnvService;
use timanthonyalexander\BaseApi\module\Page\Page;
use timanthonyalexander\BaseApi\module\Profiler\Profiler;
use timanthonyalexander\BaseApi\module\UserState\UserState;

date_default_timezone_set('Europe/Berlin');
ini_set('max_execution_time', '5');

abstract class AbstractController implements V2Controller
{
    public ResponseModel $response;

    protected int $status = 200;
    protected UserState $userState;
    protected array $trace = [];
    protected string $userId = 'anonymous';
    protected string $sessId;

    abstract protected function createResponseModel(): AbstractResponseModel;

    public function __construct(
        protected RequestModel $request,
        protected DIContainer $container,
        UserModel $user = null,
    ) {
        Profiler::start();
        $this->sessId = session_id() ?: '';

        if ($user !== null) {
            $userState = new UserState();
            $userState->userModel = $user;
            $userState->isLogin = true;
        } else {
            $userState = UserState::getState();
        }

        $this->userState = $userState;
        if (isset($userState->userModel->id)) {
            $this->userId = $userState->userModel->id;
        }

        if (!EnvService::isProduction()) {
            $this->trace[] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        Profiler::start('execute');
        $this->execute();
        Profiler::stop('execute');

        Profiler::stop();
        if (!EnvService::isProduction()) {
            $this->response->profiler = Profiler::get();
            $this->response->queries = $GLOBALS['queries'];
        }
    }

    protected function translate(
        string $token,
        bool $ucFirst = true,
        string $language = null,
    ): string {
        $translationModel = $this->container->get(TranslationModel::class);
        assert($translationModel instanceof TranslationModel);

        return $translationModel->translate($token, $ucFirst, $language);
    }

    private function execute(): void
    {
        $this->response = new ResponseModel();

        $this->setData();

        $time = microtime(true);
        if ($this->isPost()) {
            $this->postAction();
        } else {
            $this->getAction();
        }

        $this->response->status = $this->status;
        if ($this->response->headers->headers === []) {
            $this->response->headers->addHeader('Content-Type', 'application/json; charset=utf-8');
        }
        $this->response->responseTime = (int) round((microtime(true) - $time) * 1000);
        $this->response->trace = $this->trace;
        $this->response->sessId = $this->sessId;
    }

    protected function require(string ...$params): bool
    {
        $given = true;

        foreach ($params as $param) {
            if ($this->$param === null) {
                $this->response->responseMessage = sprintf(TranslationModel::getTranslation('missingRequiredParam'), $param);
                $given = false;
            }
        }

        return $given;
    }

    private function setData(): void
    {
        $params = $this->request->userParams;

        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    private function isPost(): bool
    {
        return (!empty($this->request->post) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')
            && method_exists(static::class, 'postAction');
    }

    protected function postAction(): void
    {
    }

    protected function hasPermission(string $permission): bool
    {
        return $this->userState->userModel->hasPermission($permission);
    }

    protected function wrongMethod(): void
    {
        $this->status = 405;
        $this->response->responseMessage = TranslationModel::getTranslation('method_not_allowed');
    }

    protected function notAuthenticated(): void
    {
        $this->status = 401;
        $this->response->responseMessage = TranslationModel::getTranslation('not_authenticated');
    }

    protected function notAuthorized(): void
    {
        $this->status = 403;
        $this->response->responseMessage = TranslationModel::getTranslation('not_authorized');
    }

    protected function missingParameters(): void
    {
        $this->status = 400;
        $this->response->responseMessage = TranslationModel::getTranslation('missing_parameters');
    }

    protected function notFound(): void
    {
        $this->status = 404;
        $this->response->responseMessage = TranslationModel::getTranslation('not_found');
    }

    protected function badRequest(): void
    {
        $this->status = 400;
        $this->response->responseMessage = TranslationModel::getTranslation('bad_request');
    }

    protected function data(
        array $data
    ): void {
        $responseModel = $this->createResponseModel();

        foreach ($data as $key => $value) {
            if (property_exists($responseModel, $key)) {
                $responseModel->$key = $value;
            } else {
                throw new Exception('Property ' . $key . ' does not exist in ' . get_class($responseModel));
            }
        }

        $this->response->data = array_merge($this->response->data, $responseModel->toArray());
    }

    protected function respond(
        string $responseMessage = '',
        int $status = 200,
        bool $translate = true
    ): void {
        $this->status = $status;
        $this->response->responseMessage = $translate ? TranslationModel::getTranslation($responseMessage) : $responseMessage;
    }

    abstract protected function getAction(): void;
}
