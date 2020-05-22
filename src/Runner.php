<?php
namespace Microbe;
class Runner {
    protected $request;
    protected $response;
    protected $clientEnv;
    protected $serverEnv;

    protected $module;
    protected $action;

    protected static $runner;

    protected function __construct() {
    }

    public static function run($rootPath) {
        if (isset(self::$runner)) {
            return ; // 只运行一次
        }
        self::$runner = new self();
        self::$runner->run($rootPath);
    }

    protected function run($rootPath) {
        \Microbe\Microbe::init($rootPath);

        $this->setupInputOutput();

        $this->route();

        $this->dispatch();
    }

    protected function setupInputOutput() {
        $this->setupResponse();
        $this->setupRequest();
        $this->setupClientEnv();
        $this->setupServerEnv();
    }

    protected function setupRequest() {
        $this->request = new \Microbe\Request();
    }

    protected function setupResponse() {
        $this->response = new \Microbe\Response();
    }

    protected function setupClientEnv() {
        $this->clientEnv = new \Microbe\ClientEnv();
    }

    protected function setupServerEnv() {
        $this->serverEnv = new \Microbe\ServerEnv();
    }

    protected function route() {
        $routerClass = \Microbe\Micorbe::$ins->mainApp->config->get('framework.router.action_class');
        if (empty($routerClass) || !class_exists($routerClass)) {
            $routerClass = '\\Microbe\\Router\\Static';
        }
        $router = new $routerClass();
        
        list($module, $action) = $router->route($this->request);
        if (empty($module) || empty($action)) {
            throw new RuntimeException('route error');
        }

        $this->module = $module;
        $this->action = $action;
    }

    protected function dispatch() {
        // 获取Action对象
        $namespace   = \Microbe\Microbe::$ins->mainApp->namespace . '\\Action';
        $actionClass = $namespace . '\\' . $this->module . '\\' . $this->action;
        if (!class_exists($actionClass)) {
            throw new RuntimeException("Action class[$actionClass] not found");
        }
        $actionObj = new $actionClass;

        // 检测具体的方法
        if (!($actionObj instanceof \Microbe\Action)) {
            throw new RuntimeException("Action class[{$actionClass}] is not implements \\Microbe\\Action");
        }

        // 创建模板引擎门面对象
        $templateEngine = new \Microbe\TemplateEngine\Facade();
        
        // 设置Action对象依赖的输入输出
        $actionObj->setRequest($this->request);
        $actionObj->setResponse($this->response);
        $actionObj->setClientEnv($this->clientEnv);
        $actionObj->setServerEnv($this->serverEnv);
        $actionObj->setTemplateEngine($templateEngine);

        // 执行业务逻辑
        $actionObj->execute();

        // 模板渲染并输出
        $responseBody = $templateEngine->fetch();
        $this->response->appendBody($responseBody);
        $this->response->output();
    }
}
