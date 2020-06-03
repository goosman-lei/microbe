<?php
namespace Microbe\Cli;
class Request extends \Microbe\Scalable {
    protected $originalArgv;
    protected $stdin;

    protected $opts = [];
    protected $argv = [];
    protected $argc = 0;


    public function __construct() {
        $this->originalArgv = $_SERVER['argv'];
        $this->stdin        = fopen('php://stdin', 'r');

        $this->initOptsAndArgv();
    }

    public function __get($name) {
        if ($name == 'originalArgv') {
            return $this->originalArgv;
        } else if ($name == 'stdin') {
            return $this->stdin;
        }
        return parent::__get($name);
    }

    public function getOptions() {
        return $this->opts;
    }

    public function getArgs() {
        return $this->argv;
    }

    public function getOption($name, $default = null) {
        return array_key_exists($name, $this->opts) ? $this->opts[$name] : $default;
    }

    public function hasOption($name) {
        return array_key_exists($name, $this->opts);
    }

    public function getArg($n, $default = null) {
        return $n < $this->argc ? $this->argv[$n] : $default;
    }

    public function getArgc() {
        return $this->argc;
    }

    protected function initOptsAndArgv() {
        $argv = array_slice($this->originalArgv, 1);
        $argc = count($argv);

        $idx  = 0;
        while ($idx < $argc) {
            $currArg = $argv[$idx];
            $nextArg = $idx + 1 < $argc ? $argv[$idx + 1] : null;
            if (preg_match(';^--(?<name>[\w-]++)(?:=(?<value>.*+))?$;', $currArg, $match)
                || preg_match(';^-(?<name>[a-zA-Z])(?:=(?<value>.*+))?$;', $currArg, $match)) {
                $optName = $match['name'];
                if (array_key_exists('value', $match)) {
                    $optValue = $match['value'];
                } else if (isset($nextArg) && $nextArg[0] != '-') {
                    $optValue = $nextArg;
                    $idx ++;
                } else {
                    $optValue = null;
                }
                $this->opts[$optName] = $optValue;
            } else {
                array_push($this->argv, $currArg);
            }
            $idx ++;
        }
        $this->argc = count($this->argv);
    }
}