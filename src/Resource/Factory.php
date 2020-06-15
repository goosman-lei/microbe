<?php
namespace Microbe\Resource;
class Factory {

    protected $schemes = [
        'mysqli' => '\\Microbe\\Resource\\Adapter\\Mysqli',
    ];

    protected $nodes      = [];
    protected $strategies = [];

    public function __construct($config) {
        $this->init($config);
    }

    public function get($url) {
        if (empty($this->strategies[$url])) {
            throw new \RuntimeException("Resource url [{$url}] is not configured");
        }

        $strategy = $this->strategies[$url];
        if ($strategy == 'weight') {
            $nodeConfig = \Microbe\Resource\Strategy\Weight::select($this->nodes[$url]);
        } else if ($strategy == 'random') {
            $nodeConfig = \Microbe\Resource\Strategy\Random::select($this->nodes[$url]);
        } else if (class_exists($strategy) && method_exists($strategy, 'select')) {
            $nodeConfig = call_user_func([$strategy, 'select'], $this->nodes[$url]);
        } else {
            throw new \RuntimeException("Strategy of Resource url [{$url}] is invalid");
        }

        $scheme = substr($url, 0, strpos($url, '://'));
        if (empty($this->schemes[$scheme]) || !class_exists($this->schemes[$scheme])) {
            throw new \RuntimeException("ConnectorAdapter of Resource url [{$url}] is invalid");
        }

        $connectorAdapterClass = $this->schemes[$scheme];
        return new $connectorAdapterClass();
    }

    protected function init($config) {
        /* scheme配置 */
        $this->schemes = array_merge($this->schemes, $config['scheme']);

        /* 提取节点结构 */
        foreach ($config['resource'] as $scheme => $configInfo) {
            $schemeInfo = $this->seperateConfig($configInfo);
            foreach ($schemeInfo['units'] as $businessUnit => $configInfo) {
                $businessUnitInfo = $this->seperateConfig($configInfo);
                if (!empty($businessUnitInfo['nodes'])) {
                    $url = strtolower(sprintf('%s://%s', $scheme, $businessUnit));
                    $this->nodes[$url]      = [];
                    $this->strategies[$url] = strtolower($businessUnitInfo['strategy']);

                    foreach ($businessUnitInfo['nodes'] as $nodeConfig) {
                        array_push($this->nodes[$url], array_merge($schemeInfo['config'], $businessUnitInfo['config'], $nodeConfig));
                    }
                } else {
                    foreach ($businessUnitInfo['units'] as $techUnit => $configInfo) {
                        $techUnitInfo = $this->seperateConfig($configInfo);

                        $url = strtolower(sprintf('%s://%s/%s', $scheme, $businessUnit, $techUnit));
                        $this->nodes[$url] = [];
                        $this->strategies[$url] = strtolower($techUnitInfo['strategy']);

                        foreach ($techUnitInfo['nodes'] as $nodeConfig) {
                            $this->nodes[$nodeKey] = array_merge($schemeInfo['config'], $businessUnitInfo['config'], $techUnitInfo['config'], $nodeConfig);
                        }
                    }
                }
            }
        }
    }

    protected function seperateConfig($configInfo) {
        $nodes    = [];
        $config   = [];
        $units    = [];
        $strategy = 'random';
        foreach ($configInfo as $k => $v) {
            if ($k = ':select-strategy') {
                $strategy = $v;
            } else if (is_numeric($k)) {
                array_push($nodes, $v);
            } else if ($k[0] == ':') {
                $config[substr($k, 1)] = $v;
            } else {
                $units[$k] = $v;
            }
        }

        $info = [
            'nodes'    => $nodes,
            'units'    => $units,
            'config'   => $config,
        ];

        if (!empty($nodes)) {
            $info['strategy'] = $strategy;
        }

        return $info;
    }
}