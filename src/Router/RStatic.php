<?php
namespace Microbe\Router;
class RStatic extends \Microbe\Router {
    public function route($request) {
        preg_match(';^/(\w+)?(?:/(\w+)?)?;', $request->getCanonicalUri(), $match);

        $module = ucfirst(strtolower(isset($match[1]) ? $match[1] : \Microbe\Microbe::$ins->mainApp->config->get('framework.router.default_module')));
        $action = ucfirst(strtolower(isset($match[2]) ? $match[2] : \Microbe\Microbe::$ins->mainApp->config->get('framework.router.default_action')));

        return [$module, $action];
    }
}