<?php
namespace Microbe\Chain;
class Error extends \Microbe\Chain {
    protected $failureHandlers = [];
    protected $request;
    protected $response;

    public function exec($request, $response) {
        $this->request  = $request;
        $this->response = $response;
        $response->regExtMethod('regFailureHandler', [$this, 'regFailureHandler']);
        $response->regExtMethod('doError', [$this, 'doError']);
        $response->regExtMethod('doException', [$this, 'doException']);
        $response->regExtMethod('doFailure', [$this, 'doFailure']);
        $this->doNext($request, $response);
    }

    public function regFailureHandler($callable) {
        array_push($this->failureHandlers, $callable);
    }

    public function failureHandler($failureInfo) {
        $handlers = array_reverse($this->failureHandlers);
        foreach ($handlers as $handler) {
            $handler($this->request, $this->response, $failureInfo);
        }
        $this->response->output();
        exit;
    }

    public function doError($msg, $datas = []) {
        $this->failureHandler([
            'type'  => 'error',
            'msg'   => $msg,
            'datas' => $datas,
        ]);
    }

    public function doException($msg, $datas = []) {
        $this->failureHandler([
            'type'  => 'exception',
            'msg'   => $msg,
            'datas' => $datas,
        ]);
    }

    public function doFailure($msg, $datas) {
        $this->failureHandler([
            'type'  => 'failure',
            'msg'   => $msg,
            'datas' => $datas,
        ]);
    }
}
