<?php
namespace Microbe;
class App {
    public $rootPath;

    public $config;
    public $namespace;

    public function __construct($rootPath) {
        $this->rootPath  = $rootPath;
        $this->config    = \Microbe\Config::load($rootPath . '/src/conf');
        $this->namespace = $this->config->get('app.namespace');
    }
}
