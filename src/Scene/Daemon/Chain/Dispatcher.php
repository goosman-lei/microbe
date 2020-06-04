<?php
namespace Microbe\Scene\Daemon\Chain;
class Dispatcher extends \Microbe\Chain {
    public function exec($request, $response) {
        $actionClass = '\\' . trim($this->config['namespace'], '\\') . '\\' . $request->routeModule . '\\' . $request->routeAction;
        if (!class_exists($actionClass)) {
            throw new \RuntimeException('action not exists');
        }

        $action = new $actionClass();
        $action->setRequest($request);
        $action->setResponse($response);

        $action->exec();

        // 终止类职责节点, 不进行doNext()
    }
}
