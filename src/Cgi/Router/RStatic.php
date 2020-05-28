<?php
namespace Microbe\Cgi\Router;
class RStatic implements \Microbe\Cgi\Router {
    public function route($request, $response) {
        if (!preg_match(';^/(\w+)?(?:/(\w+)?)?;', $request->canonicalUri, $match)) {
            return FALSE;
        }

        $module = ucfirst(strtolower(isset($match[1]) ? $match[1] : $this->config['default_module']));
        $action = ucfirst(strtolower(isset($match[2]) ? $match[2] : $this->config['default_action']));

        $request->regExtProperty('routeModule', $module);
        $request->regExtProperty('routeAction', $action);
        $response->regExtProperty('routeModule', $module);
        $response->regExtProperty('routeAction', $action);

        return TRUE;
    }
}
