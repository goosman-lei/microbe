<?php
namespace Microbe\Router;
class RRestful extends \Microbe\Router {
    public function route($request) {
        $eles = explode('/', $request->getCanonicalUri());

        $module = ucfirst(strtolower(isset($eles[0]) ? $eles[0] : \Microbe\Microbe::$ins->mainApp->config->get('framework.router.default_module')));
        $action = ucfirst(strtolower(isset($eles[1]) ? $eles[1] : \Microbe\Microbe::$ins->mainApp->config->get('framework.router.default_action')));
        $method = strtolower($request->getHttpMethod());

        $params = array_slice($eles, 2);
        foreach (array_slice($eles, 2) as $index => $value) {
            $request->setParam($index, $value);
        }

        return [$module, $action, $method];
    }
}
