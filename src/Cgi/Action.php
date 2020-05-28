<?php
namespace Microbe\Cgi;
abstract class Action {
    protected $request;
    protected $response;
    public function setRequest($request) {
        $this->request = $request;
    }
    public function setResponse($response) {
        $this->response = $response;
    }
    abstract public function exec();
}
