<?php
namespace Microbe;
class Microbe {
    public $config;
    public $runner;

    public static $ins;

    protected function __construct($rootPath) {
        $this->config = \Microbe\Config::load($rootPath . '/conf');
    }

    public static function init($rootPath) {
        if (isset(self::$ins)) {
            return;
        }
        self::$ins = new self($rootPath);
    }

    public function setRunner($runner) {
        $this->runner = $runner;
    }
}
