<?php
namespace Microbe\TemplateEngine\Adapter;
class Json extends \Microbe\TemplateEngine\Adapter {
    protected $datas = [];

    protected function init() {
    }

    public function assign($name, $value = null) {
        if (is_array($name)) {
            $this->datas = array_merge($this->datas, $name);
        } else {
            $this->datas[$name] = $value;
        }
    }
    public function fetch($module, $action) {
        return json_encode($this->datas, JSON_UNESCAPED_UNICODE);
    }

}
