<?php
namespace Microbe;
class App {
    protected $config;

    protected $serviceProxy;

    public function __construct($config) {
        $this->config = $config;
        $this->serviceFactory = new \Microbe\Service\ProxyFactory($config->get('app.service'));
    }
}