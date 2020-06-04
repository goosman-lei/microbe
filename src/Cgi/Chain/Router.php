<?php
namespace Microbe\Cgi\Chain;
class Router extends \Microbe\Chain {
    public function exec($request, $response) {
        $routerClass = $this->config['class'];
        $routerConfig = $this->config['config'];

        $router = new $routerClass($routerConfig);

        if ($router->route($request, $response)) {
            $this->doNext($request, $response);
        } else {
            throw new \RuntimeException('Route error');
        }
    }
}
