<?php
namespace Microbe\Cgi;
class Request extends \Microbe\Scalable {
    protected $gets;
    protected $posts;
    protected $cookies;
    protected $requests;
    protected $files;
    protected $servers;

    protected $body;
    protected $originalUri;

    public function __construct() {
        $this->initOriginalInfo();
    }

    protected function initOriginalInfo() {
        $this->gets        = $_GET;
        $this->posts       = $_POST;
        $this->cookies     = $_COOKIE;
        $this->requests    = $_REQUEST;
        $this->files       = $_FILES;
        $this->servers     = $_SERVER;
        $this->body        = file_get_contents('php://input');
        $this->originalUri = $this->getServer('REQUEST_URI', '/');
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

    public function getServers() {
        return $this->servers;
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

    public function getServer($name, $default = null) {
        return isset($this->servers[$name]) ? $this->servers[$name] : $default;
    }

    public function getBody() {
        return $this->body;
    }

    public function getOriginalUri() {
        return $this->originalUri;
    }
}