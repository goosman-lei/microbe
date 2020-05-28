<?php
namespace Microbe\Scene\Webpage\TemplateEngine;
class Factory {
    public static $factory;

    protected $config;

    protected function __construct() {
    }

    public static function init($config) {
        if (isset(self::$factory)) {
            return;
        }
        self::$factory = new self();
        self::$factory->config = $config;
    }

    public function getTemplateEngine($module, $action) {
        $engineName = $this->findEngineName($module, $action);
        return $this->newEngine($engineName);
    }

    protected function findEngineName($module, $action) {
        $config = $this->config['rules'];
        if (!isset($config[$module])) {
            return $config['*'];
        }

        $config = $config[$module];
        if (!is_array($config)) {
            return $config;
        }

        if (!isset($config[$action])) {
            return $config['*'];
        }

        return $config[$action];
    }

    protected function newEngine($engineName) {
        if (!isset($this->config['engines'][$engineName])) {
            return null;
        }
        $engineInfo = $this->config['engines'][$engineName];

        $class  = $engineInfo['class'];
        $config = $engineInfo['config'];
        if (!class_exists($class)) {
            return null;
        }

        return new $class($config);
    }
}
