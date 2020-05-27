<?php
namespace Microbe;
interface Config {
    /*
    $key = 'A.B.C.D';
    */
    public function get($key, $default);
    public function set($key, $value);
}
