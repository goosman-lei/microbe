<?php
namespace Microbe;
interface Config {
    /*
    $key = 'A.B.C.D';
    */
    public function get($key, $default = null);
    public function set($key, $value);
}
