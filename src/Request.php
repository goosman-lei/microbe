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

    protected $httpMethod;

    protected $requestId;
    protected $requestTime;

    protected $params = [];

    public function __construct() {
        $this->initOriginalInfo();
        $this->initCanonicalUri();
        $this->initRequestId();
        $this->initRequestTime();
    }

    protected function initOriginalInfo() {
        $this->gets        = $_GET;
        $this->posts       = $_POST;
        $this->cookies     = $_COOKIE;
        $this->requests    = $_REQUEST;
        $this->files       = $_FILE;
        $this->body        = file_get_contents('php://input');
        $this->originalUri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $this->httpMethod  = !empty($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
    }

    protected function initCanonicalUri() {
        $canonicalUri = $this->originalUri;
        // strip query_string and fragment. only remain path
        if (($position = strpos($canonicalUri, '?')) >= 0) {
            $canonicalUri = substr($canonicalUri, 0, $position);
        }

        // strip repeat "/"
        $canonicalUri = preg_replace(';/{2,};', '/', $canonicalUri);
        $canonicalUri = '/' . trim($canonicalUri, '/');

        $this->canonicalUri = $canonicalUri;
    }

    protected function initRequestId() {
        $this->requestId = sprintf("%017d-%s", microtime(TRUE) * 1000000, substr(md5(gethostname() . '/' . posix_getpid()), 0, 16));
    }

    protected function initRequestTime() {
        $this->requestTime = !empty($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
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

    public function getHttpMethod() {
        return $this->httpMethod;
    }

    public function getRequestId() {
        return $this->requestId;
    }

    public function getRequestTime() {
        return $this->requestTime;
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
}