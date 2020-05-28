<?php
namespace Microbe\Scene\Webpage\TemplateEngine;
class Phtml extends Abs {

    protected $tplDatas = array();

    protected $extName = '.phtml';

    protected function init() {
        if (array_key_exists('ext_name', $this->config)) {
            $this->extName = $this->config['ext_name'];
        }
    }

    public function assign($name, $value = null) {
        if (is_array($name)) {
            $this->tplDatas = array_merge($this->tplDatas, $name);
        } else {
            $this->tplDatas[strval($name)] = $value;
        }
    }

    public function render($tplPath) {
        if (!empty($this->config['template_dir'])) {
            $tplPath = rtrim($this->config['template_dir'], '/') . '/' . $tplPath;
        }
        if (!empty($this->extName)) {
            $tplPath = $tplPath . $this->extName;
        }
        return $this->realRender($tplPath);
    }

    protected function realRender($tplPath) {
        if (!is_file($tplPath)) {
            throw new RuntimeException("phtml template file [{$tplPath}] does not exists");
        }
        ob_start();
        extract($this->tplDatas);
        @include($tplPath);
        return ob_get_clean();
    }
}
