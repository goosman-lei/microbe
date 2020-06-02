<?php
namespace Microbe\Chain;
class Error extends \Microbe\Chain {

    protected $request;
    protected $response;

    protected $failureHandlers = [];

    public function exec($request, $response) {
        $this->request  = $request;
        $this->response = $response;

        $response->regExtMethod('regFailureHandler', [$this, 'regFailureHandler']);
        $response->regExtMethod('abortError', [$this, 'abortError']);
        $response->regExtMethod('abortException', [$this, 'abortException']);
        $response->regExtMethod('abortNormal', [$this, 'abortNormal']);

        $this->listenInternalError($request, $response);

        $this->regFailureHandler([$this, 'systemFailureHandler']);

        try {
            $this->doNext($request, $response);
        } catch (\Exception $exception) {
            $response->abortException($exception);
        }
    }

    protected function listenInternalError($request, $response) {
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) use($response) {
            $response->abortError($errno, $errstr, $errfile, $errline, $errcontext);
        }, E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
    }

    public function regFailureHandler($callable) {
        array_push($this->failureHandlers, $callable);
    }

    public function abortError($errno, $errstr, $errfile, $errline, $errcontext = []) {
        $errInfo = [
            'type' => 'error',
            'code' => $errno,
            'msg'  => $errstr,
            'file' => $errfile, 
            'line' => $errline,
            'data' => [
                'errcontext' => $errcontext,
            ],
        ];

        $this->doFailure($errInfo);
    }

    public function abortException($exception) {
        $errInfo = [
            'type' => 'exception',
            'code' => $exception->getCode(),
            'msg'  => $exception->getMessage(),
            'file' => $exception->getFile(), 
            'line' => $exception->getLine(),
            'data' => [
                'trace' => $exception->getTrace(),
            ],
        ];

        $this->doFailure($errInfo);
    }

    public function abortNormal($msg, $code = 0, $data = []) {
        $btrace  = debug_backtrace();
        array_shift($btrace);
        array_shift($btrace);
        $traceInfo = array_shift($btrace);

        $errInfo = [
            'type' => 'error',
            'code' => $code,
            'msg'  => $msg,
            'file' => $traceInfo['file'],
            'line' => $traceInfo['line'],
            'data' => $data,
        ];

        $this->doFailure($errInfo);
    }

    protected function doFailure($errInfo) {
        $handlers = array_reverse($this->failureHandlers);
        foreach ($handlers as $handler) {
            if ($handler($errInfo, $this->request, $this->response)) {
                $this->response->output();
                exit;
            }
        }
    }

    public function systemFailureHandler($errInfo, $request, $response) {
        $response->appendBody(json_encode($errInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return TRUE;
    }
}