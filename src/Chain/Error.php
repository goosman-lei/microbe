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

        $this->listenError();

        $this->doNext($request, $response);
    }

    public function listenError() {
        $response = $this->response;
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) use($response) {
            $response->doError($errno, $errstr, $errfile, $errline, $errcontext);
        }, E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
        set_exception_handler(function($exception) use($response) {
            $response->doException($exception);
        });
    }

    public function systemFailureHandler($request, $response, $failureInfo) {
        $response->appendBody(json_encode($failureInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return TRUE;
    }

    public function regFailureHandler($callable) {
        array_push($this->failureHandlers, $callable);
    }

    public function failureHandler($failureInfo) {
        $handlers = array_reverse($this->failureHandlers);
        foreach ($handlers as $handler) {
            if ($handler($this->request, $this->response, $failureInfo)) {
                $this->response->output();
                exit;
            }
        }
    }

    public function doError($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->failureHandler([
            'type'  => 'error',
            'msg'   => $errstr,
            'file'  => $errfile,
            'line'  => $errline,
            'datas' => [
                'errno'      => $errno,
                'errcontext' => $errcontext,
            ],
        ]);
    }

    public function doException($exception) {
        $this->failureHandler([
            'type'  => 'exception',
            'msg'   => $exception->getMessage(),
            'file'  => $exception->getFile(),
            'line'  => $exception->getLine(),
            'datas' => [
                'code'  => $exception->getCode(),
                'trace' => $exception->getTrace(),
            ],
        ]);
    }

    public function doFailure($msg, $datas) {
        $btrace = debug_backtrace();
        array_shift($btrace);
        array_shift($btrace);
        $trace  = array_shift($btrace);
        $this->failureHandler([
            'type'  => 'failure',
            'msg'   => $msg,
            'file'  => $trace['file'],
            'line'  => $trace['line'],
            'datas' => $datas,
        ]);
    }
}
