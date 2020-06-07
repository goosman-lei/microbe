<?php
namespace Microbe\Service\Proxy;
class Internal {
    protected $service;

    protected $serviceObject;

    public function __construct($service) {
        $this->service = $service;

        $this->initServiceObject();
    }

    protected function initServiceObject() {
        $namespace = \Microbe\Microbe::$ins->workApp->config->get('app.namespace');
        $className = '\\' . rtrim($namespace, '\\') . '\\Service\\' . $this->service;

        $this->serviceObject = new $className();
    }

    public function __call($name, $arguments) {
        if (!method_exists($this->serviceObject, $name)) {
            return [
                'code' => \Microbe\Service\Error::SERVICE_NOT_FOUND,
                'msg'  => "Service method [{$this->service}->{$name}] not found",
                'data' => [],
            ];
        }
        try {
            $serviceData = call_user_func_array([$this->serviceObject, $name], $arguments);
            return [
                'code' => \Microbe\Service\Error::SUCCESS,
                'msg'  => "Service method [{$this->service}->{$name}] success",
                'data' => $serviceData,
            ];
        } catch (\Exception $exception) {
            return [
                'code' => \Microbe\Service\Error::SERVICE_EXCEPTION,
                'msg'  => "Service method [{$this->service}->{$name}] throw exception",
                'data' => [
                    'exception' => $exception,
                ],
            ];
        }
    }
}
