<?php
namespace Microbe;
class Microbe {
    public $config;
    public $runner;

    protected $hooks = [];

    public static $ins;

    protected function __construct() {
    }

    public static function init($rootPath, $runner) {
        if (isset(self::$ins)) {
            return;
        }
        self::$ins = new self();
        self::$ins->_init($rootPath, $runner);
    }

    protected function _init($rootPath, $runner) {
        $this->config = \Microbe\Config::load($rootPath . '/conf');
        $this->runner = $runner;

        $this->initHooks();
    }

    protected function initHooks() {
        $configs = $this->config->get('hooks');
        foreach ($configs as $hookName => $hookConfig) {
            $hookClass = $hookConfig['class'];
            array_push($this->hooks, new $hookClass($hookConfig['config']));
        }
    }

    public function prependHook($class, $config = []) {
        array_unshift($this->hooks, new $class($config));
    }

    public function appendHook($class, $config = []) {
        array_push($this->hooks, new $class($config));
    }

    public function positiveApplyHooks($method) {
        $argv = func_get_args();
        $argv = array_slice($argv, 1);
        foreach ($this->hooks as $hook) {
            call_user_func_array([$hook, $method], $argv);
        }
    }

    public function negativeApplyHooks($method) {
        $argv = func_get_args();
        $argv = array_slice($argv, 1);
        foreach ($this->hooks as $hook) {
            call_user_func_array([$hook, $method], $argv);
        }
    }

}
