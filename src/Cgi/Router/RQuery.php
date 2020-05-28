<?php
namespace Microbe\Cgi\Router;
class RQuery implements \Microbe\Cgi\Router {
    public function route($request, $response) {
        $argNameModule = $this->config['arg_name_module'] ?: 'module';
        $argNameAction = $this->config['arg_name_action'] ?: 'action';

        $module = ucfirst(strtolower($request->getQuery($argNameModule, $this->config['default_module'])));
        $action = ucfirst(strtolower($request->getQuery($argNameAction, $this->config['default_action'])));

        $request->regExtProperty('routeModule', $module);
        $request->regExtProperty('routeAction', $action);
        $response->regExtProperty('routeModule', $module);
        $response->regExtProperty('routeAction', $action);

        return TRUE;
    }
}
