<?php
namespace Microbe\TemplateEngine;
abstract class Adapter {
    protected $config;
    public function __construct($config) {
        $this->config = $config;
        $this->init();
    }
    abstract protected function init();
    abstract public function assign($name, $value = null);
    abstract public function fetch($module, $action);
}
