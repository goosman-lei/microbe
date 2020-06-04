<?php
namespace Microbe\Scene\Webpage\Chain;
class Dispatcher extends \Microbe\Chain {
    public function exec($request, $response) {
        $actionNamespace = $this->config['namespace'];
        $actionClass     = '\\' . trim($actionNamespace, '\\') . '\\' . $request->routeModule . '\\' . $request->routeAction;
        if (!class_exists($actionClass)) {
            throw new \RuntimeException('action not exists');
        }

        $action = new $actionClass();
        $action->setRequest($request);
        $action->setResponse($response);

        $action->exec();
    }
}
