<?php
namespace Microbe\Service;
class Factory {
    protected $config;
    public function __construct($config) {
        $this->config = $config;
    }

    public function get($module, $service) {
        if (!array_key_exists($module, $this->config)) {
            return new \Microbe\Service\Proxy\NotFound(
                \Microbe\Service::CODE_MODULE_NOT_FOUND,
                "Service module [{$module}] not config"
            );
        }

        $moduleConfig = $this->config[$module];
        if ($moduleConfig['proxy'] == 'composer') {
            return new \Microbe\Service\Proxy\Composer($moduleConfig['config'], $service);
        } else if (class_exists($moduleConfig['proxy'])) {
            $proxyClass = $moduleConfig['proxy'];
            return new $proxyClass($moduleConfig['config'], $service);
        }

        return new \Microbe\Service\Proxy\NotFound(
                \Microbe\Service::CODE_MODULE_NOT_FOUND,
                "Service module [proxy = {$moduleConfig['proxy']}] is invalid"
        );
    }
}
