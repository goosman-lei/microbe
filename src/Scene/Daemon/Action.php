<?php
namespace Microbe\Scene\Daemon;
abstract class Action {
    protected $request;
    protected $response;
    final public function setRequest($request) {
        $this->request = $request;
    }
    final public function setResponse($response) {
        $this->response = $response;
    }
    abstract public function exec();
}
