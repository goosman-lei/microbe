<?php
namespace Microbe\Scene\Daemon\Chain;
class Router extends \Microbe\Chain {
    public function exec($request, $response) {
        $routerClass = $this->config['class'];
        $routerConfig = $this->config['config'];

        $router = new $routerClass($routerConfig);
        $router->route($request, $response);

        $this->doNext($request, $response);
    }
}
