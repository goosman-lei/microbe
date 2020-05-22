<?php
namespace Microbe\Router;
class RQuery extends \Microbe\Router {
    public function route($request) {
        $moduleArgName = \Microbe\Microbe::$ins->mainApp->config->get('framework.router.module_arg_name');
        if (empty($moduleArgName)) {
            $moduleArgName = 'module';
        }
        $actionArgName = \Microbe\Microbe::$ins->mainApp->config->get('framework.router.action_arg_name');
        if (empty($actionArgName)) {
            $actionArgName = 'action';
        }

        $module = ucfirst(strtolower($request->getQuery($moduleArgName, \Microbe\Microbe::$ins->mainApp->config->get('framework.router.config.default_module'))));
        $action = ucfirst(strtolower($request->getQuery($actionArgName, \Microbe\Microbe::$ins->mainApp->config->get('framework.router.config.default_action'))));

        return [$module, $action];
    }
}
