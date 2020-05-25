<?php
namespace Microbe;
class Microbe {
    public $config;

    public static $ins;

    protected function __construct($rootPath) {
        $this->config = new \Microbe\Config($rootPath . '/conf');
    }

    public static function init($rootPath) {
        if (isset(self::$ins)) {
            return;
        }
        self::$ins = new self($rootPath);
    }
}
