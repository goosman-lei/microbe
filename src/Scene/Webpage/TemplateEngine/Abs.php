<?php
namespace Microbe\Scene\Webpage\TemplateEngine;
abstract class Abs {
    protected $config = [];

    public function __construct($config = []) {
        $this->config = $config;
        $this->init();
    }

    abstract protected function init();

    abstract public function assign($name, $value = null);

    abstract public function render($tpl);
}
