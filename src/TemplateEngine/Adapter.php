<?php
namespace Microbe\TemplateEngine;
abstract class Adapter implements \Microbe\TemplateEngine {
    protected $config;
    public function __construct($config) {
        $this->config = $config;
        $this->init();
    }
    protected function init();
    public function assign($name, $value = null);
    public function fetch($module, $action);
}
