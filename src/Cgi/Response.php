<?php
namespace Microbe\Cgi;
class Response {
    protected $bodyBuffer = '';
    protected $headers = [];
    protected $cookies = [];

    protected function output() {
        foreach ($this->cookies as $cookie) {
            call_user_func_array('setcookie', $cookie);
        }

        foreach ($this->headers as $header) {
            header("{$header[0]}: {$header[1]}");
        }

        echo $this->bodyBuffer;
    }

    public function success() {
        $this->output();
        exit;
    }

    public function failure() {
        $this->output();
        exit;
    }

    public function appendBody($text) {
        $this->bodyBuffer .= $text;
    }

    public function setBody($text) {
        $this->bodyBuffer = $text;
    }

    public function getBody() {
        return $this->bodyBuffer;
    }

    public function clearBody() {
        $bodyBuffer = $this->bodyBuffer;
        $this->bodyBuffer = '';
        return $bodyBuffer;
    }

    public function addCookie($name, $value = '', $expire = 0, $path = '', $domain = '', $secure = FALSE, $httponly = FALSE) {
        $this->cookies[] = [$name, $value, $expire, $path, $domain, $secure, $httponly];
    }

    public function clearCookie($callable) {
        foreach ($this->cookies as $index => $cookie) {
            if ($callable($cookie)) {
                unset($this->cookies[$index]);
            }
        }
        $this->cookies = array_values($this->cookies);
    }

    public function addHeader($name, $value) {
        $this->headers[] = [$name, $value];
    }

    public function clearHeader($callable) {
        foreach ($this->headers as $index => $header) {
            if ($callable($header)) {
                unset($this->headers[$index]);
            }
        }
        $this->headers = array_values($this->headers);
    }
}