<?php
namespace Microbe;
abstract class Router {
    protected $config = [];
    public function __construct($config = []) {
        $this->config = $config;
    }
    abstract public function route($request, $response);
}
