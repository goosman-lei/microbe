<?php
namespace Microbe;
class Scalable {
    protected $params = [];
    protected $extMethods = [];
    protected $extProperties = [];

    public function getParams() {
        return $this->params;
    }

    public function getParam($name, $default = null) {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    public function setParam($k, $v) {
        $this->params[$k] = $v;
    }

    public function regExtProperty($name, $value) {
        $this->extProperties[$name] = $value;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->extProperties)) {
            return $this->extProperties[$name];
        }
    }

    public function regExtMethod($name, $callable) {
        $this->extMethods[$name] = $callable;
    }

    public function __call($name, $arguments) {
        if (isset($this->extMethods[$name]) && is_callable($this->extMethods[$name])) {
            $extMethod = $this->extMethods[$name];
            return call_user_func_array($extMethod, $arguments);
        }
    }
}
