<?php
namespace Microbe;
class Microbe {
    public $mainApp;

    public static $ins;

    protected function __construct($rootPath) {
        $this->mainApp = new \Microbe\App($rootPath);
    }

    public static function init($rootPath) {
        self::$ins = new self($rootPath);
    }
}
