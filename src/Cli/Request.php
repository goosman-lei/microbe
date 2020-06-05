<?php
namespace Microbe\Cli;
class Request extends \Microbe\Scalable {
    protected $originalArgs;

    protected $opts = [];
    protected $args = [];
    protected $argc = 0;

    public function __construct() {
        $this->originalArgs = $_SERVER['argv'];

        $this->initOptsAndArgs();
    }

    public function getOriginalArgs() {
        return $this->originalArgs;
    }

    public function getOptions() {
        return $this->opts;
    }

    public function getArgs() {
        return $this->args;
    }

    public function getOption($name, $default = null) {
        return array_key_exists($name, $this->opts) ? $this->opts[$name] : $default;
    }

    public function hasOption($name) {
        return array_key_exists($name, $this->opts);
    }

    public function getArg($n, $default = null) {
        return $n < $this->argc ? $this->args[$n] : $default;
    }

    public function getArgc() {
        return $this->argc;
    }
    
    public function getEnv($name = null, $default = null) {
        if (!isset($name)) {
            return getenv();
        }
        return getenv($name) ?: $default;
    }

    protected function initOptsAndArgs() {
        $argv = array_slice($this->originalArgs, 1);
        $argc = count($argv);

        $idx = 0;
        while ($idx < $argc) {
            $currArg = $argv[$idx];
            $nextArg = $idx + 1 < $argc ? $argv[$idx + 1] : null;

            if (preg_match(';^--(?<name>[\w-]++)(?:=(?<value>.*+))?$;', $currArg, $match)
                || preg_match(';^-(?<name>[a-zA-Z])(?:=(?<value>.*+))?$;', $currArg, $match)) {
                $optName = $match['name'];
                if (array_key_exists('value', $match)) {
                    $optValue = $match['value'];
                } else if ($nextArg[0] == '-') {
                    // 无值
                    $optValue = null;
                } else {
                    // 有值
                    $optValue = $nextArg;
                    $idx ++;
                }
                $this->opts[$optName] = $optValue;
            } else {
                array_push($this->args, $currArg);
            }

            $idx ++;
        }
    }
}