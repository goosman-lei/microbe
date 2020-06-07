<?php
namespace Microbe;
class App {
    public $config;
    public $serviceFactory;

    public function __construct($config) {
        $this->config = $config;
        $this->serviceFactory = new \Microbe\Service\Factory($config->get('app.service'));
    }
}