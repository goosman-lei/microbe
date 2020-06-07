<?php
namespace Microbe\Service\Proxy;
class NotFound {
    protected $code;
    protected $msg;
    protected $data;
    public function __construct($code, $msg = '', $data = []) {
        $this->code = $code;
        $this->msg  = $msg;
        $this->data = $data;
    }
    public function __call($name, $arguments) {
        return [
            'code' => $this->code,
            'msg'  => $this->msg,
            'data' => $this->data,
        ];
    }
}
