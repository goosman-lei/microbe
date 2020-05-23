<?php
namespace Microbe\TemplateEngine;
class Facade implements \Microbe\TemplateEngine {
    protected $engine;

    protected $module;
    protected $action;

    /*
    配置规则
    [
        'engines' => [
            'smarty' => [
                'adapter' => '\Microbe\TemplateEngine\Adapter\Smarty',
                'config'  => [],
            ],
            'json' => [
                'adapter' => '\Microbe\TemplateEngine\Adapter\Json',
                'config'  => [],
            ],
        ],
        'routers' => [
            '-'      => 'smarty',          # 全局默认Smarty
            'module_a' => [
                '-'        => 'json',      # /module_a 下所有action没有特殊配置则默认Json
                'action_1' => 'smarty',    # /module_a/action_1 使用Smarty
                'action_2' => 'phtml',     # /module_a/action_1 使用Phtml
                'action_3' => 'smarty',    # /module_a/action_1 使用Smarty
            ],
            'module_b' => [
                '-' => 'json',             # /module_b 下所有action没有特殊配置则默认Json
            ],
        ],
    ]
    */
    public function __construct($module, $action) {
        $this->module = $module;
        $this->action = $action;

        $adapterName   = $this->getAdapterName();
        $adapterInfo   = \Microbe\Microbe::$ins->mainApp->config->get('app.template_engine.engines.' . $adapterName);
        $adapterClass  = $adapterInfo['adapter'];
        $this->engine  = new $adapterClass($adapterInfo['config']);
    }

    protected function getAdapterName() {
        $moduleConfig = \Microbe\Microbe::$ins->mainApp->config->get('app.template_engine.routers.' . $this->module);
        if (empty($moduleConfig)) {
            return \Microbe\Microbe::$ins->mainApp->config->get('app.template_engine.routers.-');
        }

        if (empty($moduleConfig[$this->action])) {
            return $moduleConfig['-'];
        }

        return $moduleConfig[$this->action];
    }

    public function assign($name, $value = null) {
        $this->engine->assign($name, $value);
    }

    public function fetch() {
        return $this->engine->fetch($this->module, $this->action);
    }
}
