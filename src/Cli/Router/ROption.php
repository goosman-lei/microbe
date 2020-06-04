<?php
namespace Microbe\Cli\Router;
class ROption extends \Microbe\Router {
    public function route($request, $response) {
        $optionNameModule = !empty($this->config['option_name_module']) ? $this->config['option_name_module'] : 'module';
        $optionNameAction = !empty($this->config['option_name_action']) ? $this->config['option_name_action'] : 'action';
        $module = $request->getOption($optionNameModule);
        $action = $request->getOption($optionNameAction);
        if (empty($module) || empty($action)) {
            return FALSE;
        }

        $module = ucfirst(strtolower($module));
        $action = ucfirst(strtolower($action));

        $request->regExtProperty('routeModule', $module);
        $request->regExtProperty('routeAction', $action);
        $response->regExtProperty('routeModule', $module);
        $response->regExtProperty('routeAction', $action);
        return TRUE;
    }
}
