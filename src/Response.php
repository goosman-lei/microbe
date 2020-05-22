<?php
namespace Microbe;
class Response {
    protected $bodyBuffer = '';
    protected $headers = [];
    protected $cookies = [];

    public function output() {
        foreach ($this->cookies as $cookie) {
            call_user_func_array('setcookie', $cookie);
        }

        foreach ($this->headers as $header) {
            header($header);
        }

        echo $this->bodyBuffer;
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

    public function addHeader($header) {
        $this->header[] = $header;
    }
}
