<?php
namespace Microbe;
class Request {
    protected $gets;
    protected $posts;
    protected $cookies;
    protected $requests;
    protected $files;

    protected $body;
    protected $originalUri;

    protected $params = [];
    protected $extMethods = [];
    protected $extProperties = [];

    public function __construct() {
        $this->initOriginalInfo();
    }

    protected function initOriginalInfo() {
        $this->gets        = $_GET;
        $this->posts       = $_POST;
        $this->cookies     = $_COOKIE;
        $this->requests    = $_REQUEST;
        $this->files       = $_FILES;
        $this->body        = file_get_contents('php://input');
        $this->originalUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    }

    public function getQuries() {
        return $this->gets;
    }

    public function getPosts() {
        return $this->posts;
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function getRequests() {
        return $this->requests;
    }

    public function getFiles() {
        return $this->files;
    }

    public function getQuery($name, $default = null) {
        return isset($this->gets[$name]) ? $this->gets[$name] : $default;
    }

    public function getPost($name, $default = null) {
        return isset($this->posts[$name]) ? $this->posts[$name] : $default;
    }

    public function getRequest($name, $default = null) {
        return isset($this->requests[$name]) ? $this->requests[$name] : $default;
    }

    public function getCookie($name, $default = null) {
        return isset($this->cookies[$name]) ? $this->cookies[$name] : $default;
    }

    public function getFile($name, $default = null) {
        return isset($this->files[$name]) ? $this->files[$name] : $default;
    }

    public function getBody() {
        return $this->body;
    }

    public function getOriginalUri() {
        return $this->originalUri;
    }

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
        if (array_key_exists($this->extProperties[$name])) {
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