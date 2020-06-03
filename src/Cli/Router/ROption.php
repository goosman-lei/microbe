<?php
namespace Microbe\Cli\Router;
class ROption extends \Microbe\Router {
    public function route($request, $response) {
        $argNameModule = $this->config['option_name_module'] ?: 'module';
        $argNameAction = $this->config['option_name_action'] ?: 'action';

        $module = ucfirst(strtolower($request->getOption($argNameModule)));
        $action = ucfirst(strtolower($request->getQuery($argNameAction)));

        $request->regExtProperty('routeModule', $module);
        $request->regExtProperty('routeAction', $action);
        $response->regExtProperty('routeModule', $module);
        $response->regExtProperty('routeAction', $action);

        return TRUE;
    }
}
