<?php
namespace Microbe\Service\Proxy;
class Internal {
    protected $config;
    protected $service;

    protected static $workApps = [];
    protected $serviceObj;

    public function __construct($config, $service) {
        $this->config  = $config;
        $this->service = $service;

        $this->initServiceObject();
    }

    protected function initServiceObject() {
        $prevWorkApp = \Microbe\Microbe::$ins->workApp;
        \Microbe\Microbe::$ins->switchWorkApp($this->getApp());

        $namespace = \Microbe\Microbe::$ins->workApp->config->get('app.namespace');
        $className = '\\' . rtrim($namespace, '\\') . '\\Service\\' . $this->service;

        try {
            $this->serviceObject = new $className();
        } finally {
            \Microbe\Microbe::$ins->switchWorkApp($prevWorkApp);
        }
    }

    protected function getApp() {
        $key = $this->config['group'] . '/' . $this->config['project'];
        if (isset(self::$workApps[$key])) {
            return self::$workApps[$key];
        }

        $vendorPath = \Microbe\Microbe::$ins->mainApp->config->get('app.vendor_path');
        $config     = new \Microbe\Config($vendorPath . '/' . $this->config['group'] . '/' . $this->config['protect'] . '/src/conf');
        $app        = new \Microbe\App($config);

        self::$workApps[$key] = $app;
        return $app;
    }

    public function __call($name, $arguments) {
        if (!method_exists($this->serviceObject, $name)) {
            return [
                'code' => \Microbe\Service\Error::SERVICE_NOT_FOUND,
                'msg'  => "Service method [{$this->service}->{$name}] not found",
                'data' => [],
            ];
        }

        $prevWorkApp = \Microbe\Microbe::$ins->workApp;
        \Microbe\Microbe::$ins->switchWorkApp($this->getApp());
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
        } finally {
            \Microbe\Microbe::$ins->switchWorkApp($prevWorkApp);
        }
    }
}
