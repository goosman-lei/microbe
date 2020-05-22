<?php
namespace Microbe;
/*
职责:
1. 通过request/clientEnv/serverEnv获取输入
2. 通过\Micobe\Microbe::$ins框架实例获取辅助工具协助
3. 编写业务逻辑
4. 将结果数据assign给templateEngine
*/
abstract class Action {
    protected $templateEngine;
    protected $request;
    protected $response;
    protected $clientEnv;
    protected $serverEnv;

    public function __construct() {
    }

    public function setRequest($request) {
        $this->request = $request;
    }
    public function setResponse($response) {
        $this->response = $response;
    }
    public function setClientEnv($clientEnv) {
        $this->clientEnv = $clientEnv;
    }
    public function setServerEnv($serverEnv) {
        $this->serverEnv = $serverEnv;
    }
    public function setTemplateEngine($templateEngine) {
        $this->templateEngine = $templateEngine;
    }

    public function execute();
}
