<?php
namespace Microbe\Cli\Router;
class ROption extends \Microbe\Router {
    public function route($request, $response) {
        $module = $request->getOption($this->config['option_name_module'] ?: 'module');
        $action = $request->getOption($this->config['option_name_action'] ?: 'action');
        if (empty($module) || empty($action)) {
            return FALSE;
        }

        $request->regExtProperty('routeModule', $module);
        $request->regExtProperty('routeAction', $action);
        $response->regExtProperty('routeModule', $module);
        $response->regExtProperty('routeAction', $action);
        return TRUE;
    }
}
