<?php
namespace Microbe\Resource;
class Factory {

    protected $schemes = [
        'mysqli' => '\\Microbe\\Resource\\Adapter\\Mysqli',
    ];

    protected $nodes  = [];
    protected $strategies = [];

    public function __construct($config) {
        $this->init($config);
    }

    protected function init($config) {
        if (empty($config['scheme'])) {
            $config['scheme'] = [];
        }
        if (empty($config['resource'])) {
            $config['resource'] = [];
        }

        $this->schemes = array_merge($this->schemes, $config['schemes']);

        foreach ($config['resource'] as $scheme => $schemeInfo) {
            $schemeConfig = $this->seperateConfig($schemeInfo);
            foreach ($schemeConfig['units'] as $businessUnit => $businessUnitInfo) {
                $businessUnitConfig = $this->seperateConfig($businessUnitInfo);

                if (empty($businessUnitConfig['nodes'])) {
                    foreach ($businessUnitConfig['units'] as $techUnit => $techUnitInfo) {
                        $techUnitConfig = $this->seperateConfig($techUnitInfo);

                        $url = strtolower(sprintf("%s://%s/%s", $scheme, $businessUnit, $techUnit));
                        $this->nodes[$url]      = [];
                        $this->strategies[$url] = $techUnitConfig['strategy'];

                        foreach ($techUnitConfig['nodes'] as $nodeConfig) {
                            array_push($this->nodes[$url], array_merge($schemeConfig['config'], $businessUnitConfig['config'], $techUnitConfig['config'], $nodeConfig));
                        }
                    }
                } else {
                    $url = strtolower(sprintf("%s://%s", $scheme, $businessUnit));
                    $this->nodes[$url]      = [];
                    $this->strategies[$url] = $businessUnitConfig['strategy'];

                    foreach ($businessUnitConfig['nodes'] as $nodeConfig) {
                        array_push($this->nodes[$url], array_merge($schemeConfig['config'], $businessUnitConfig['config'], $nodeConfig));
                    }
                }
            }
        }
    }

    protected function seperateConfig($info) {
        $units    = [];
        $config   = [];
        $nodes    = [];
        $strategy = 'random';
        foreach ($info as $k => $v) {
            if ($k === ':select-strategy') {
                $strategy = strtolower($v);
            } else if (is_int($k)) {
                array_push($nodes, $v);
            } else if ($k[0] === ':') {
                $config[substr($k, 1)] = $v;
            } else {
                $units[$k] = $v;
            }
        }

        $rInfo = [
            'units'  => $units,
            'config' => $config,
            'nodes'  => $nodes,
        ];
        if (!empty($nodes)) {
            $rInfo['strategy'] = $strategy;
        }
        return $rInfo;
    }

    public function get($url) {
        $url = strtolower($url);
        if (empty($this->nodes[$url]) || empty($this->strategies[$url])) {
            return \RuntimeException("Resource url [{$url}] is not configured");
        }

        $strategy = $this->strategies[$url];
        if ($strategy == 'random') {
            $nodeConfig = \Microbe\Resource\Strategy\Random::select($this->nodes[$url]);
        } else if ($strategy == 'weight') {
            $nodeConfig = \Microbe\Resource\Strategy\Weight::select($this->nodes[$url]);
        } else if (class_exists($strategy) && method_exists($strategy, 'select')) {
            $nodeConfig = call_user_func([$strategy, 'select'], $this->nodes[$url]);
        } else {
            return \RuntimeException("Strategy of Resource url [{$url}] is invalid");
        }

        $scheme = strtolower(substr($url, 0, strpos($url, '://')));
        if (!array_key_exists($scheme, $this->schemes)) {
            return \RuntimeException("Scheme of Resource url [{$url}] is not found");
        }
        $connectorAdapterClass = $this->schemes[$scheme];
        if (!class_exists($connectorAdapterClass)) {
            return \RuntimeException("ConnectorAdapter class of Resource url [{$url}] is not found");
        }

        return new $connectorAdapterClass($nodeConfig);
    }
}
