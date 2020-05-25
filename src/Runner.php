<?php
namespace Microbe;
class Runner {
    public $request;
    public $response;
    public $clientEnv;
    public $serverEnv;

    protected static $runner;

    protected function __construct() {
    }

    public static function run($rootPath) {
        if (isset(self::$runner)) {
            return ; // 只运行一次
        }
        self::$runner = new self();
        self::$runner->_run($rootPath);
    }

    protected function _run($rootPath) {
        \Microbe\Microbe::init($rootPath);
        \Microbe\Microbe::$ins->setRunner($this);

        $this->setupInputOutput();
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
}
