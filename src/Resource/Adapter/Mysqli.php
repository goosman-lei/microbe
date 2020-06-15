<?php
namespace Microbe\Resource\Adapter;
class Mysqli {
    protected $config;
    protected $mysqli;

    protected $errstr;

    public function __construct($config) {
        $this->config = $config;
    }

    protected function getConn() {
        if (isset($this->mysqli)) {
            return $this->mysqli;
        }

        $mysqli  = new \Mysqli();

        if (isset($this->config['connect_timeout'])) {
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, intval($this->config['connect_timeout']));
        }

        // 连接
        @$mysqli->real_connect($this->config['host'], $this->config['user'], $this->config['passwd'], $this->config['dbname'], $this->config['port']);
        if ($mysqli->connect_errno) {
            $this->errstr = sprintf('mysqliAdapter getConn error when real_connect[%d]: %s', $mysqli->connect_errno, $mysqli->connect_error);
            return null;
        }

        if (isset($options['charset'])) {
            $mysqli->set_charset($options['charset']);
            if ($mysqli->errno) {
                $this->errstr = sprintf('mysqliAdapter getConn error when set_charset[%d]: %s', $mysqli->errno, $mysqli->error);
                return null;
            }
        }

        if (isset($options['collation'])) {
            $mysqli->query('SET collation_connection = ' . $options['collation']);
            if ($mysqli->errno) {
                $this->errstr = sprintf('mysqliAdapter getConn error when set_collation[%d]: %s', $mysqli->errno, $mysqli->error);
                return null;
            }
        }

        return $this->mysqli = $mysqli;
    }

    public function __get($name) {
        $mysqli = $this->getConn();
        if (isset($mysqli)) {
            return $mysqli->$name;
        }
    }

    public function __call($name, $arguments) {
        $mysqli = $this->getConn();
        if (isset($mysqli)) {
            return call_user_func_array([$mysqli, $name], $arguments);
        }
    }
}
