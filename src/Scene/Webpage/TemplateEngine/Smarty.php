<?php
namespace Microbe\Scene\Webpage\TemplateEngine;
class Smarty extends Abs {

    protected $smarty;

    protected $extName = '.tpl';

    protected function init() {
        $this->smarty = new \Smarty();

        $smartyConfig = $this->config;

        if (array_key_exists('ext_name', $smartyConfig)) {
            $this->extName = $smartyConfig['ext_name'];
            unset($smartyConfig['ext_name']);
        }

        foreach ($smartyConfig as $k => $v) {
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

    public function render($tplPath) {
        if (!empty($this->extName)) {
            $tplPath .= $this->extName;
        }
        return $this->smarty->fetch($tplPath, NULL, NULL, NULL, FALSE);
    }
}
