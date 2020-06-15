<?php
namespace Microbe;
class App {
    public $config;
    public $serviceFactory;
    public $resourceFactory;

    public function __construct($config) {
        $this->config = $config;
        $this->serviceFactory  = new \Microbe\Service\Factory($config->get('app.service'));
        $this->resourceFactory = new \Microbe\Resource\Factory($config->get('app.resource'));
    }
}