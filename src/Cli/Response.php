<?php
namespace Microbe\Cli;
class Response extends \Microbe\Scalable {
    protected $stdout;
    protected $stderr;

    public function __construct() {
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');
    }

    public function printf($fmt) {
        $argv = func_get_args();
        array_unshift($argv, $this->stdout);
        call_user_func_array('fprintf', $argv);
    }

    public function eprintf($fmt) {
        $argv = func_get_args();
        array_unshift($argv, $this->stderr);
        call_user_func_array('fprintf', $argv);
    }
}
