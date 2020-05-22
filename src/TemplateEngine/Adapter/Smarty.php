<?php
namespace Microbe\TemplateEngine\Adapter;
class Smarty extends \Microbe\TemplateEngine\Adapter {
    protected $smarty;

    protected function init() {
        $this->smarty = new \Smarty();
        foreach ($this->config as $k => $v) {
            if ($k == 'template_dir') {
                $this->smarty->setTemplateDir($v);
            } else if ($k == 'compile_dir') {
                $this->smarty->setCompileDir($v);
            } else if ($k == 'config_dir') {
                $this->smarty->setConfigDir($v);
            } else if ($k == 'cache_dir') {
                $this->smarty->setCacheDir($v);
            } else if ($k == 'plugin_dir') {
                $this->smarty->setPluginsDir($v);
            } else {
                $this->smarty->$k = $v;
            }
        }
    }
    public function assign($name, $value = null) {
        return $this->smarty->assign($name, $value);
    }
    public function fetch($module, $action) {
        $tplPath = "/$module/$action";
        $tplPath = isset($this->config['ext_name']) ? $tplPath . $this->config['ext_name'] : $tplPath;
        return $this->smarty->fetch($tplPath);
    }

}
