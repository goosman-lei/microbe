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
    protected $canonicalUri;

    protected $params = [];
    protected $extMethods = [];

    public function __construct() {
        $this->initOriginalInfo();
        $this->initCanonicalUri();
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

    protected function initCanonicalUri() {
        $canonicalUri = $this->originalUri;
        // strip query_string and fragment. only remain path
        if (($position = strpos($canonicalUri, '?')) !== FALSE) {
            $canonicalUri = substr($canonicalUri, 0, $position);
        }

        // strip repeat "/"
        $canonicalUri = preg_replace(';/{2,};', '/', $canonicalUri);
        $canonicalUri = '/' . trim($canonicalUri, '/');

        $baseUri = '/' . trim(\Microbe\Microbe::$ins->config->get('app.base_uri'), '/');
        if ($baseUri != '/' && strpos($canonicalUri, $baseUri) === 0) {
            $canonicalUri = substr($canonicalUri, strlen($baseUri));
        }

        $this->canonicalUri = $canonicalUri;
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

    public function getCanonicalUri() {
        return $this->canonicalUri;
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

    public function regExtMethod($name, $callable) {
        $this->extMethods[$name] = $callable;
    }

    public function __call($name, $arguments) {
        if (isset($this->extMethods[$name]) && is_callable($this->extMethods[$name])) {
            $extMethod = $this->extMethods[$name];
            call_user_func_array($extMethod, $arguments);
        }
    }
}