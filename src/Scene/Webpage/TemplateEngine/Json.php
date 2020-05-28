<?php
namespace Microbe\Scene\Webpage\TemplateEngine;
class Json extends Abs {

    protected $tplDatas = array();

    public function init() {
    }

    public function assign($name, $value = null) {
        if (is_array($name)) {
            $this->tplDatas = array_merge($this->tplDatas, $name);
        } else {
            $this->tplDatas[strval($name)] = $value;
        }
    }
    public function render($tplPath) {
        $options = isset($this->config['json_encode_options']) ? $this->config['json_encode_options'] : 0;
        return json_encode($this->tplDatas, $options);
    }
}
