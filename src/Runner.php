<?php
namespace Microbe;
class Runner {
    public $request;
    public $response;

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
        $this->setupResponse();
        $this->setupRequest();

        \Microbe\Microbe::init($rootPath, $this);
        \Microbe\Microbe::prependHook('\Microbe\Hook\CanonicalUri');

        \Microbe\Microbe::$ins->positiveApplyHooks('afterInput', $request);
    }

    protected function setupRequest() {
        $this->request = new \Microbe\Request();
    }
}
