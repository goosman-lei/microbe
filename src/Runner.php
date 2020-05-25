<?php
namespace Microbe;
class Runner {
    public $request;

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
        $this->setupRequest();

        \Microbe\Microbe::init($rootPath, $this);
        \Microbe\Microbe::$ins->prependHook('\Microbe\Hook\CanonicalUri');

        \Microbe\Microbe::$ins->positiveApplyHooks('afterInput', $this->request);
    }

    protected function setupRequest() {
        $this->request = new \Microbe\Request();
    }
}
